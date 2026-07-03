<?php

namespace Tests\Feature;

use App\Models\AnonymousSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_analytics_loads_only_when_configured(): void
    {
        config(['services.google_analytics.id' => null]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('googletagmanager.com/gtag/js', false);

        config(['services.google_analytics.id' => 'G-TEST123']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('googletagmanager.com/gtag/js?id=G-TEST123', false)
            ->assertSee("gtag('config', \"G-TEST123\")", false);
    }

    public function test_recaptcha_markup_loads_only_when_site_key_is_configured(): void
    {
        config(['services.recaptcha.site_key' => null]);

        $this->get(route('register'))
            ->assertOk()
            ->assertDontSee('g-recaptcha-response', false);

        config(['services.recaptcha.site_key' => 'site-key']);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('g-recaptcha-response', false)
            ->assertSee('api.js?render=site-key', false)
            ->assertSee('action: "register"', false);
    }

    public function test_register_requires_valid_recaptcha_when_configured(): void
    {
        $this->configureRecaptcha();

        $this->post(route('register'), [
            'name' => 'Blocked',
            'email' => 'blocked@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('recaptcha');

        $this->assertDatabaseMissing('users', ['email' => 'blocked@example.com']);

        $this->fakeRecaptcha(['success' => true, 'score' => 0.9, 'action' => 'register']);

        $this->post(route('register'), [
            'name' => 'Allowed',
            'email' => 'allowed@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'g-recaptcha-response' => 'valid-token',
        ])->assertRedirect(route('home'));

        $this->assertDatabaseHas('users', ['email' => 'allowed@example.com']);
    }

    public function test_login_rejects_low_score_or_wrong_action_recaptcha(): void
    {
        $this->configureRecaptcha();
        User::factory()->create([
            'email' => 'person@example.com',
            'password' => 'password123',
        ]);

        $this->fakeRecaptcha(['success' => true, 'score' => 0.4, 'action' => 'login']);

        $this->post(route('login'), [
            'email' => 'person@example.com',
            'password' => 'password123',
            'g-recaptcha-response' => 'low-score',
        ])->assertSessionHasErrors('recaptcha');

        $this->fakeRecaptcha(['success' => true, 'score' => 0.9, 'action' => 'register']);

        $this->post(route('login'), [
            'email' => 'person@example.com',
            'password' => 'password123',
            'g-recaptcha-response' => 'wrong-action',
        ])->assertSessionHasErrors('recaptcha');

        $this->assertGuest();
    }

    public function test_guest_anonymous_submission_requires_recaptcha_but_authenticated_entry_creation_does_not(): void
    {
        $this->configureRecaptcha();

        $this->post(route('entries.store'), [
            'term' => 'guest blocked',
            'meaning' => 'Blocked meaning.',
        ])->assertSessionHasErrors('recaptcha');

        $this->fakeRecaptcha(['success' => true, 'score' => 0.9, 'action' => 'anonymous_entry_submit']);

        $this->post(route('entries.store'), [
            'term' => 'guest allowed',
            'meaning' => 'Allowed meaning.',
            'g-recaptcha-response' => 'valid-token',
        ])->assertRedirect(route('entries.create'));

        $this->assertDatabaseHas('anonymous_submissions', [
            'term' => 'guest allowed',
            'status' => AnonymousSubmission::STATUS_PENDING,
        ]);

        $user = User::factory()->create();
        Http::fake();

        $this->actingAs($user)->post(route('entries.store'), [
            'term' => 'signed allowed',
            'meaning' => 'Signed meaning.',
        ])->assertRedirect();

        Http::assertNothingSent();
        $this->assertDatabaseHas('entries', ['term' => 'signed allowed']);
    }

    private function configureRecaptcha(): void
    {
        config([
            'services.recaptcha.site_key' => 'site-key',
            'services.recaptcha.secret_key' => 'secret-key',
            'services.recaptcha.min_score' => 0.5,
        ]);
    }

    private function fakeRecaptcha(array $response): void
    {
        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response($response),
        ]);
    }
}
