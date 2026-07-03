<?php

namespace Tests\Feature;

use App\Jobs\GenerateEntrySocialImage;
use App\Models\AnonymousSubmission;
use App\Models\Definition;
use App\Models\Entry;
use App\Models\User;
use App\Services\EntrySocialImageGenerator;
use App\Support\NormalizesTurkmenText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SocialImageGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_entry_creation_dispatches_social_image_job(): void
    {
        Queue::fake();

        $user = User::factory()->create();

        $this->actingAs($user)->post(route('entries.store'), [
            'term' => 'täze söz',
            'meaning' => 'Täze many.',
        ])->assertRedirect();

        Queue::assertPushed(GenerateEntrySocialImage::class);
    }

    public function test_definition_creation_dispatches_social_image_job(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $entry = Entry::create([
            'user_id' => $user->id,
            'term' => 'bar söz',
            'slug' => 'bar-soz',
            'normalized_term' => NormalizesTurkmenText::normalize('bar söz'),
        ]);

        $this->actingAs($user)->post(route('definitions.store', $entry), [
            'meaning' => 'Goşmaça many.',
        ])->assertRedirect();

        Queue::assertPushed(GenerateEntrySocialImage::class);
    }

    public function test_anonymous_publication_dispatches_social_image_job(): void
    {
        Queue::fake();

        $submission = AnonymousSubmission::makePending([
            'term' => 'anon söz',
            'meaning' => 'Anonim many.',
        ]);

        $this->actingAs($this->eligibleUser())
            ->post(route('moderation.anonymous-submissions.vote', $submission), ['vote' => 'approve']);
        $this->actingAs($this->eligibleUser())
            ->post(route('moderation.anonymous-submissions.vote', $submission), ['vote' => 'approve']);

        Queue::assertPushed(GenerateEntrySocialImage::class);
    }

    public function test_generator_creates_utf8_png_when_gd_is_available(): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('GD extension is not installed.');
        }

        $user = User::factory()->create();
        $entry = Entry::create([
            'user_id' => $user->id,
            'term' => 'Türkmen ýörite söz',
            'slug' => 'turkmen-yorite-soz',
            'normalized_term' => NormalizesTurkmenText::normalize('Türkmen ýörite söz'),
        ]);
        Definition::create([
            'entry_id' => $entry->id,
            'user_id' => $user->id,
            'meaning' => 'Ýörite türkmen harplary bilen many: ä, ö, ü, ý, ç, ş, ň.',
        ]);

        app(EntrySocialImageGenerator::class)->generate($entry);

        $entry->refresh();
        $path = public_path($entry->og_image_path);
        $size = getimagesize($path);

        $this->assertFileExists($path);
        $this->assertSame([1200, 630], [$size[0], $size[1]]);

        @unlink($path);
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
