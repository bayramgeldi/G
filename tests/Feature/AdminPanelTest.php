<?php

namespace Tests\Feature;

use App\Filament\Resources\DefinitionResource;
use App\Filament\Resources\EntryResource;
use App\Filament\Resources\UserResource;
use App\Models\Definition;
use App\Models\Entry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_panel(): void
    {
        $this->get('/admin')
            ->assertRedirect('/admin/login');
    }

    public function test_non_admin_user_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_admin_user_can_access_admin_panel(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();
    }

    public function test_admin_resources_are_read_only(): void
    {
        $user = User::factory()->create();
        $entry = Entry::create([
            'user_id' => $user->id,
            'term' => 'hidden admin term',
            'slug' => 'hidden-admin-term',
            'normalized_term' => 'hidden admin term',
            'is_hidden' => true,
        ]);
        $definition = Definition::create([
            'entry_id' => $entry->id,
            'user_id' => $user->id,
            'meaning' => 'Hidden meaning.',
            'is_hidden' => true,
        ]);

        $this->assertTrue(UserResource::canView($user));
        $this->assertTrue(EntryResource::canView($entry));
        $this->assertTrue(DefinitionResource::canView($definition));

        $this->assertFalse(UserResource::canCreate());
        $this->assertFalse(EntryResource::canEdit($entry));
        $this->assertFalse(DefinitionResource::canDelete($definition));
    }
}
