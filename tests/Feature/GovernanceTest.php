<?php

namespace Tests\Feature;

use App\Models\Appeal;
use App\Models\Definition;
use App\Models\Entry;
use App\Models\User;
use App\Support\NormalizesTurkmenText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GovernanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_ineligible_users_cannot_report(): void
    {
        [$entry] = $this->content();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('entries.report', $entry), ['reason' => 'spam'])
            ->assertForbidden();
    }

    public function test_eligible_users_can_report_once(): void
    {
        [$entry] = $this->content();
        $user = $this->eligibleUser();

        $this->actingAs($user)
            ->post(route('entries.report', $entry), ['reason' => 'spam'])
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('entries.report', $entry), ['reason' => 'spam'])
            ->assertSessionHasErrors('report');

        $this->assertDatabaseCount('moderation_reports', 1);
    }

    public function test_content_hides_after_report_threshold_and_logs_event(): void
    {
        [$entry] = $this->content();

        foreach (range(1, 5) as $i) {
            $this->actingAs($this->eligibleUser())
                ->post(route('entries.report', $entry), ['reason' => 'spam'])
                ->assertRedirect();
        }

        $this->assertTrue($entry->fresh()->is_hidden);
        $this->assertDatabaseHas('moderation_events', [
            'event_type' => 'report_threshold_hide',
            'subject_type' => Entry::class,
            'subject_id' => $entry->id,
        ]);
        $this->get('/')->assertDontSee($entry->term);
    }

    public function test_only_author_can_open_appeal_and_eligible_users_can_restore(): void
    {
        [$entry, $definition, $author] = $this->content();
        $definition->update(['is_hidden' => true]);

        $this->actingAs($this->eligibleUser())
            ->post(route('definitions.appeal', $definition), ['statement' => 'Restore it'])
            ->assertForbidden();

        $this->actingAs($author)
            ->post(route('definitions.appeal', $definition), ['statement' => 'This is real slang.'])
            ->assertRedirect();

        $appeal = Appeal::firstOrFail();

        foreach (range(1, 3) as $i) {
            $this->actingAs($this->eligibleUser())
                ->post(route('appeals.vote', $appeal), ['vote' => 'restore'])
                ->assertRedirect();
            $appeal->refresh();
        }

        $this->assertFalse($definition->fresh()->is_hidden);
        $this->assertSame('restored', $appeal->fresh()->status);
        $this->assertDatabaseHas('moderation_events', [
            'event_type' => 'appeal_restored',
            'subject_type' => Definition::class,
            'subject_id' => $definition->id,
        ]);
    }

    public function test_emergency_hide_is_logged_and_public_log_is_visible(): void
    {
        [$entry] = $this->content();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->patch(route('admin.entries.hide', $entry))
            ->assertRedirect();

        $this->assertTrue($entry->fresh()->is_hidden);
        $this->assertDatabaseHas('moderation_events', [
            'event_type' => 'emergency_hide',
            'actor_type' => 'admin',
        ]);

        $this->get(route('governance.log'))
            ->assertOk()
            ->assertSee(__('app.event_emergency_hide'));
    }

    public function test_rules_and_export_are_public_and_export_excludes_hidden_content(): void
    {
        [$entry, $definition] = $this->content();
        $hiddenEntry = Entry::create([
            'user_id' => $entry->user_id,
            'term' => 'hidden word',
            'slug' => 'hidden-word',
            'normalized_term' => 'hidden word',
            'is_hidden' => true,
        ]);
        $definition->update(['votes_count' => 4]);

        $this->get(route('governance.rules'))->assertOk();

        $this->getJson(route('export.json'))
            ->assertOk()
            ->assertJsonFragment(['term' => $entry->term])
            ->assertJsonMissing(['term' => $hiddenEntry->term])
            ->assertJsonFragment(['votes_count' => 4]);
    }

    private function content(): array
    {
        $author = User::factory()->create(['created_at' => now()->subDays(8)]);
        $entry = Entry::create([
            'user_id' => $author->id,
            'term' => 'governance slang',
            'slug' => 'governance-slang',
            'normalized_term' => NormalizesTurkmenText::normalize('governance slang'),
        ]);
        $definition = Definition::create([
            'entry_id' => $entry->id,
            'user_id' => $author->id,
            'meaning' => 'Community meaning.',
        ]);

        return [$entry, $definition, $author];
    }

    private function eligibleUser(): User
    {
        $user = User::factory()->create(['created_at' => now()->subDays(8)]);
        Entry::create([
            'user_id' => $user->id,
            'term' => 'contribution '.$user->id,
            'slug' => 'contribution-'.$user->id,
            'normalized_term' => 'contribution '.$user->id,
        ]);

        return $user;
    }
}
