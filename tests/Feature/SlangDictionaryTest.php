<?php

namespace Tests\Feature;

use App\Models\Definition;
use App\Models\DictionaryAlias;
use App\Models\DictionaryWord;
use App\Models\Entry;
use App\Models\User;
use App\Support\NormalizesTurkmenText;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SlangDictionaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_browse_entries(): void
    {
        $user = User::factory()->create();
        $entry = Entry::create([
            'user_id' => $user->id,
            'term' => 'gaty gowy',
            'slug' => 'gaty-gowy',
            'normalized_term' => NormalizesTurkmenText::normalize('gaty gowy'),
        ]);
        $entry->definitions()->create([
            'user_id' => $user->id,
            'meaning' => 'Örän gowy zat.',
        ]);

        $this->get('/')->assertOk()->assertSee('gaty gowy');
        $this->get(route('entries.show', $entry))->assertOk()->assertSee('Örän gowy zat.');
    }

    public function test_authenticated_users_can_create_entries_and_definitions(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('entries.store'), [
            'term' => 'bomba',
            'meaning' => 'Gaty täsirli zat.',
            'example' => 'Bu aýdym bomba.',
        ]);

        $entry = Entry::first();
        $response->assertRedirect(route('entries.show', $entry));
        $this->assertDatabaseHas('entries', ['normalized_term' => 'bomba']);
        $this->assertDatabaseHas('definitions', ['meaning' => 'Gaty täsirli zat.']);
    }

    public function test_users_can_upvote_once_and_definitions_sort_by_votes(): void
    {
        $author = User::factory()->create();
        $voter = User::factory()->create();
        $entry = Entry::create([
            'user_id' => $author->id,
            'term' => 'salam',
            'slug' => 'salam',
            'normalized_term' => 'salam',
        ]);
        $low = Definition::create(['entry_id' => $entry->id, 'user_id' => $author->id, 'meaning' => 'Pes many']);
        $high = Definition::create(['entry_id' => $entry->id, 'user_id' => $author->id, 'meaning' => 'Ýokary many']);
        $high->update(['votes_count' => 2]);

        $this->actingAs($voter)->post(route('definitions.vote', $low))->assertRedirect();
        $this->actingAs($voter)->post(route('definitions.vote', $low))->assertRedirect();
        $this->assertSame(1, $low->fresh()->votes_count);

        $this->get(route('entries.show', $entry))
            ->assertSeeInOrder(['Ýokary many', 'Pes many']);
    }

    public function test_leaderboard_ranks_users_by_contributions(): void
    {
        $active = User::factory()->create(['name' => 'Active']);
        $quiet = User::factory()->create(['name' => 'Quiet']);
        $entry = Entry::create([
            'user_id' => $active->id,
            'term' => 'zor',
            'slug' => 'zor',
            'normalized_term' => 'zor',
        ]);
        Definition::create(['entry_id' => $entry->id, 'user_id' => $active->id, 'meaning' => 'Bir']);
        Definition::create(['entry_id' => $entry->id, 'user_id' => $active->id, 'meaning' => 'Iki']);
        Definition::create(['entry_id' => $entry->id, 'user_id' => $quiet->id, 'meaning' => 'Üç']);

        $this->get(route('leaderboard'))->assertOk()->assertSeeInOrder(['Active', 'Quiet']);
    }

    public function test_hidden_content_is_not_public(): void
    {
        $user = User::factory()->create();
        $entry = Entry::create([
            'user_id' => $user->id,
            'term' => 'gizlin',
            'slug' => 'gizlin',
            'normalized_term' => 'gizlin',
            'is_hidden' => true,
        ]);

        $this->get('/')->assertDontSee('gizlin');
        $this->get(route('entries.show', $entry))->assertNotFound();
    }

    public function test_dictionary_lookup_uses_exact_and_alias_matches(): void
    {
        $word = DictionaryWord::create([
            'headword' => 'kitap',
            'normalized_headword' => 'kitap',
            'meaning' => 'Okalýan eser.',
        ]);
        DictionaryAlias::create([
            'dictionary_word_id' => $word->id,
            'alias' => 'kitaby',
            'normalized_alias' => 'kitaby',
        ]);

        $this->getJson(route('dictionary.lookup', ['word' => 'Kitap']))
            ->assertOk()
            ->assertJsonPath('headword', 'kitap');

        $this->getJson(route('dictionary.lookup', ['word' => 'kitaby']))
            ->assertOk()
            ->assertJsonPath('meaning', 'Okalýan eser.');
    }
}
