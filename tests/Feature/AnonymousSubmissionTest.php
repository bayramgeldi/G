<?php

namespace Tests\Feature;

use App\Models\AnonymousSubmission;
use App\Models\Definition;
use App\Models\Entry;
use App\Models\User;
use App\Support\NormalizesTurkmenText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnonymousSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_submit_pending_anonymous_words(): void
    {
        $this->get(route('entries.create'))
            ->assertOk()
            ->assertSee(__('app.anonymous_submission_notice'));

        $this->post(route('entries.store'), [
            'term' => 'anon slang',
            'meaning' => 'Anonymous meaning.',
            'example' => 'Anonymous example.',
        ])->assertRedirect(route('entries.create'));

        $this->assertDatabaseHas('anonymous_submissions', [
            'term' => 'anon slang',
            'status' => AnonymousSubmission::STATUS_PENDING,
        ]);
        $this->assertDatabaseMissing('entries', [
            'normalized_term' => NormalizesTurkmenText::normalize('anon slang'),
        ]);
    }

    public function test_authenticated_users_still_publish_immediately(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('entries.store'), [
            'term' => 'signed slang',
            'meaning' => 'Signed meaning.',
        ])->assertRedirect();

        $this->assertDatabaseHas('entries', [
            'normalized_term' => NormalizesTurkmenText::normalize('signed slang'),
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseCount('anonymous_submissions', 0);
    }

    public function test_only_reviewers_can_access_and_vote_on_anonymous_queue(): void
    {
        $submission = $this->submission();
        $user = User::factory()->create();

        $this->get(route('moderation.anonymous-submissions'))
            ->assertRedirect(route('login'));

        $this->actingAs($user)
            ->get(route('moderation.anonymous-submissions'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('moderation.anonymous-submissions.vote', $submission), ['vote' => 'approve'])
            ->assertForbidden();

        $this->actingAs($this->eligibleUser())
            ->get(route('moderation.anonymous-submissions'))
            ->assertOk()
            ->assertSee($submission->term);
    }

    public function test_anonymous_submission_publishes_after_two_approvals_and_sixty_six_percent(): void
    {
        $submission = $this->submission(['term' => 'publish me']);

        $this->actingAs($this->eligibleUser())
            ->post(route('moderation.anonymous-submissions.vote', $submission), ['vote' => 'approve'])
            ->assertRedirect();

        $this->assertSame(AnonymousSubmission::STATUS_PENDING, $submission->fresh()->status);

        $this->actingAs($this->eligibleUser())
            ->post(route('moderation.anonymous-submissions.vote', $submission), ['vote' => 'approve'])
            ->assertRedirect(route('moderation.anonymous-submissions'));

        $submission->refresh();
        $this->assertSame(AnonymousSubmission::STATUS_PUBLISHED, $submission->status);
        $this->assertDatabaseHas('entries', [
            'term' => 'publish me',
            'user_id' => null,
        ]);
        $this->assertDatabaseHas('definitions', [
            'meaning' => $submission->meaning,
            'user_id' => null,
        ]);
    }

    public function test_reject_vote_blocks_publication_until_approval_ratio_recovers(): void
    {
        $submission = $this->submission();

        $this->actingAs($this->eligibleUser())
            ->post(route('moderation.anonymous-submissions.vote', $submission), ['vote' => 'approve'])
            ->assertRedirect();
        $this->actingAs($this->eligibleUser())
            ->post(route('moderation.anonymous-submissions.vote', $submission), ['vote' => 'reject'])
            ->assertRedirect();

        $this->assertSame(AnonymousSubmission::STATUS_PENDING, $submission->fresh()->status);

        $this->actingAs($this->eligibleUser())
            ->post(route('moderation.anonymous-submissions.vote', $submission), ['vote' => 'approve'])
            ->assertRedirect(route('moderation.anonymous-submissions'));

        $this->assertSame(AnonymousSubmission::STATUS_PUBLISHED, $submission->fresh()->status);
    }

    public function test_reviewers_can_vote_once(): void
    {
        $submission = $this->submission();
        $reviewer = $this->eligibleUser();

        $this->actingAs($reviewer)
            ->post(route('moderation.anonymous-submissions.vote', $submission), ['vote' => 'approve'])
            ->assertRedirect();

        $this->actingAs($reviewer)
            ->post(route('moderation.anonymous-submissions.vote', $submission), ['vote' => 'reject'])
            ->assertSessionHasErrors('anonymous_submission');

        $this->assertDatabaseCount('anonymous_submission_votes', 1);
    }

    public function test_approval_for_existing_word_adds_anonymous_definition(): void
    {
        $author = User::factory()->create();
        $entry = Entry::create([
            'user_id' => $author->id,
            'term' => 'known',
            'slug' => 'known',
            'normalized_term' => 'known',
        ]);
        $submission = $this->submission([
            'term' => 'known',
            'meaning' => 'Anonymous extra meaning.',
        ]);

        $this->actingAs($this->eligibleUser())
            ->post(route('moderation.anonymous-submissions.vote', $submission), ['vote' => 'approve']);
        $this->actingAs($this->eligibleUser())
            ->post(route('moderation.anonymous-submissions.vote', $submission), ['vote' => 'approve']);

        $this->assertDatabaseCount('entries', 3);
        $this->assertSame($entry->id, $submission->fresh()->published_entry_id);
        $this->assertDatabaseHas('definitions', [
            'entry_id' => $entry->id,
            'meaning' => 'Anonymous extra meaning.',
            'user_id' => null,
        ]);
    }

    public function test_published_anonymous_content_displays_as_anonymous_and_stays_off_leaderboard(): void
    {
        $submission = $this->submission();
        $this->actingAs($this->eligibleUser())
            ->post(route('moderation.anonymous-submissions.vote', $submission), ['vote' => 'approve']);
        $this->actingAs($this->eligibleUser())
            ->post(route('moderation.anonymous-submissions.vote', $submission), ['vote' => 'approve']);

        $entry = $submission->fresh()->publishedEntry;

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(__('app.anonymous'));
        $this->get(route('entries.show', $entry))
            ->assertOk()
            ->assertSee(__('app.anonymous'));
        $this->get(route('leaderboard'))
            ->assertOk()
            ->assertDontSee('<div class="truncate font-bold">'.__('app.anonymous').'</div>', false);
    }

    public function test_rules_document_anonymous_approval_policy(): void
    {
        $this->get(route('governance.rules'))
            ->assertOk()
            ->assertSee(__('app.rule_anonymous_review'));
    }

    private function submission(array $attributes = []): AnonymousSubmission
    {
        return AnonymousSubmission::makePending([
            'term' => $attributes['term'] ?? 'anon term',
            'meaning' => $attributes['meaning'] ?? 'Anonymous meaning.',
            'example' => $attributes['example'] ?? null,
        ]);
    }

    private function eligibleUser(): User
    {
        $user = User::factory()->create(['created_at' => now()->subDays(8)]);
        Entry::create([
            'user_id' => $user->id,
            'term' => 'reviewer '.$user->id,
            'slug' => 'reviewer-'.$user->id,
            'normalized_term' => 'reviewer '.$user->id,
        ]);

        return $user;
    }
}
