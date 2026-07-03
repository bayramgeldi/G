<?php

namespace Tests\Feature;

use App\Models\Definition;
use App\Models\Entry;
use App\Models\User;
use App\Support\NormalizesTurkmenText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoMetadataTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_default_seo_and_open_graph_tags(): void
    {
        config(['app.url' => 'https://example.test']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<meta name="description" content="'.e(__('app.seo_home_description')).'">', false)
            ->assertSee('<link rel="canonical" href="'.route('home').'">', false)
            ->assertSee('<meta name="robots" content="index, follow">', false)
            ->assertSee('<meta property="og:type" content="website">', false)
            ->assertSee('<meta property="og:title" content="'.e(__('app.app_name')).'">', false)
            ->assertSee('<meta name="twitter:card" content="summary">', false)
            ->assertDontSee('og:image', false);
    }

    public function test_optional_open_graph_image_is_rendered_when_configured(): void
    {
        config(['services.seo.og_image_url' => 'https://example.test/og.png']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<meta property="og:image" content="https://example.test/og.png">', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
            ->assertSee('<meta name="twitter:image" content="https://example.test/og.png">', false);
    }

    public function test_entry_page_uses_term_specific_metadata_from_visible_definition(): void
    {
        $user = User::factory()->create();
        $entry = Entry::create([
            'user_id' => $user->id,
            'term' => 'seo term',
            'slug' => 'seo-term',
            'normalized_term' => NormalizesTurkmenText::normalize('seo term'),
        ]);
        Definition::create([
            'entry_id' => $entry->id,
            'user_id' => $user->id,
            'meaning' => '<strong>Meaning with markup</strong> and useful context.',
        ]);

        $this->get(route('entries.show', $entry))
            ->assertOk()
            ->assertSee('<meta property="og:type" content="article">', false)
            ->assertSee('<meta property="og:title" content="seo term | '.e(__('app.app_name')).'">', false)
            ->assertSee('<link rel="canonical" href="'.route('entries.show', $entry).'">', false)
            ->assertSee('Meaning with markup and useful context.', false)
            ->assertDontSee('<strong>Meaning with markup</strong>', false);
    }

    public function test_entry_page_uses_generated_social_image_metadata(): void
    {
        $user = User::factory()->create();
        $entry = Entry::create([
            'user_id' => $user->id,
            'term' => 'ýörite söz',
            'slug' => 'yorite-soz',
            'normalized_term' => NormalizesTurkmenText::normalize('ýörite söz'),
            'og_image_path' => 'og/entries/yorite-soz.png',
        ]);
        Definition::create([
            'entry_id' => $entry->id,
            'user_id' => $user->id,
            'meaning' => 'Ýörite many.',
        ]);

        $this->get(route('entries.show', $entry))
            ->assertOk()
            ->assertSee('<meta property="og:image" content="http://localhost/og/entries/yorite-soz.png">', false)
            ->assertSee('<meta property="og:image:width" content="1200">', false)
            ->assertSee('<meta property="og:image:height" content="630">', false)
            ->assertSee('<meta property="og:image:alt" content="ýörite söz sözüniň Göçme Manyly Sözler Sözlügindäki many kartasy">', false)
            ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
            ->assertSee('<meta name="twitter:image" content="http://localhost/og/entries/yorite-soz.png">', false);
    }

    public function test_utility_pages_are_noindexed(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        $this->get(route('entries.create'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_public_content_pages_are_indexable(): void
    {
        $this->get(route('governance.rules'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="index, follow">', false)
            ->assertSee('<meta name="description" content="'.e(__('app.seo_rules_description')).'">', false);

        $this->get(route('roadmap'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="index, follow">', false)
            ->assertSee('<meta name="description" content="'.e(__('app.seo_roadmap_description')).'">', false);
    }
}
