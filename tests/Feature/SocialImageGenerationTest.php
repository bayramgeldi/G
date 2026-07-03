<?php

namespace Tests\Feature;

use App\Models\AnonymousSubmission;
use App\Models\Definition;
use App\Models\Entry;
use App\Models\User;
use App\Services\EntrySocialImageGenerator;
use App\Support\NormalizesTurkmenText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class SocialImageGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_entry_creation_generates_social_image(): void
    {
        $this->mock(EntrySocialImageGenerator::class, function ($mock) {
            $mock->shouldReceive('generate')
                ->once()
                ->with(Mockery::on(fn ($entry) => $entry instanceof Entry && $entry->term === 'fresh term'));
        });

        $user = User::factory()->create();

        $this->actingAs($user)->post(route('entries.store'), [
            'term' => 'fresh term',
            'meaning' => 'Fresh meaning.',
        ])->assertRedirect();
    }

    public function test_definition_creation_generates_social_image(): void
    {
        $user = User::factory()->create();
        $entry = Entry::create([
            'user_id' => $user->id,
            'term' => 'bar soz',
            'slug' => 'bar-soz',
            'normalized_term' => NormalizesTurkmenText::normalize('bar soz'),
        ]);
        $this->mock(EntrySocialImageGenerator::class, function ($mock) use ($entry) {
            $mock->shouldReceive('generate')
                ->once()
                ->with(Mockery::on(fn ($generatedEntry) => $generatedEntry instanceof Entry && $generatedEntry->is($entry)));
        });

        $this->actingAs($user)->post(route('definitions.store', $entry), [
            'meaning' => 'Extra meaning.',
        ])->assertRedirect();
    }

    public function test_anonymous_publication_generates_social_image(): void
    {
        $submission = AnonymousSubmission::makePending([
            'term' => 'anon soz',
            'meaning' => 'Anonymous meaning.',
        ]);
        $this->mock(EntrySocialImageGenerator::class, function ($mock) {
            $mock->shouldReceive('generate')
                ->once()
                ->with(Mockery::on(fn ($entry) => $entry instanceof Entry && $entry->term === 'anon soz'));
        });

        $this->actingAs($this->eligibleUser())
            ->post(route('moderation.anonymous-submissions.vote', $submission), ['vote' => 'approve']);
        $this->actingAs($this->eligibleUser())
            ->post(route('moderation.anonymous-submissions.vote', $submission), ['vote' => 'approve']);
    }

    public function test_generator_creates_png_when_gd_is_available(): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('GD extension is not installed.');
        }

        $user = User::factory()->create();
        $entry = Entry::create([
            'user_id' => $user->id,
            'term' => 'Turkmen yorite soz',
            'slug' => 'turkmen-yorite-soz',
            'normalized_term' => NormalizesTurkmenText::normalize('Turkmen yorite soz'),
        ]);
        Definition::create([
            'entry_id' => $entry->id,
            'user_id' => $user->id,
            'meaning' => 'Meaning with enough context for the generated image.',
        ]);

        app(EntrySocialImageGenerator::class)->generate($entry);

        $entry->refresh();
        $path = public_path($entry->og_image_path);
        $size = getimagesize($path);

        $this->assertFileExists($path);
        $this->assertSame([1200, 630], [$size[0], $size[1]]);

        @unlink($path);
    }

    public function test_regenerate_social_images_command_processes_visible_entries_with_definitions(): void
    {
        $user = User::factory()->create();
        $entry = Entry::create([
            'user_id' => $user->id,
            'term' => 'command term',
            'slug' => 'command-term',
            'normalized_term' => NormalizesTurkmenText::normalize('command term'),
        ]);
        Definition::create([
            'entry_id' => $entry->id,
            'user_id' => $user->id,
            'meaning' => 'Command generated meaning.',
        ]);
        $hiddenEntry = Entry::create([
            'user_id' => $user->id,
            'term' => 'hidden command term',
            'slug' => 'hidden-command-term',
            'normalized_term' => NormalizesTurkmenText::normalize('hidden command term'),
            'is_hidden' => true,
        ]);
        Definition::create([
            'entry_id' => $hiddenEntry->id,
            'user_id' => $user->id,
            'meaning' => 'Hidden meaning.',
        ]);
        Entry::create([
            'user_id' => $user->id,
            'term' => 'definitionless command term',
            'slug' => 'definitionless-command-term',
            'normalized_term' => NormalizesTurkmenText::normalize('definitionless command term'),
        ]);

        $this->mock(EntrySocialImageGenerator::class, function ($mock) use ($entry) {
            $mock->shouldReceive('generate')
                ->once()
                ->with(Mockery::on(fn ($generatedEntry) => $generatedEntry instanceof Entry && $generatedEntry->is($entry)));
        });

        $this->artisan('entries:regenerate-social-images')
            ->assertExitCode(0);
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
