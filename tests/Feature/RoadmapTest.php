<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoadmapTest extends TestCase
{
    use RefreshDatabase;

    public function test_roadmap_page_is_public_and_describes_future_features(): void
    {
        $this->get(route('roadmap'))
            ->assertOk()
            ->assertSee('APA style citation')
            ->assertSee('references')
            ->assertSee('region');
    }

    public function test_top_navigation_links_to_roadmap(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(route('roadmap', absolute: false), false)
            ->assertSee('Roadmap');
    }
}
