<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_admin_login(): void
    {
        $this->get(route('admin.leads.index'))->assertRedirect(route('login'));
        $this->post(route('admin.logout'))->assertRedirect(route('login'));
    }

    public function test_regular_users_cannot_view_contacts(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.leads.index'))
            ->assertForbidden();
    }

    public function test_only_admin_credentials_allow_login(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $regularUser = User::factory()->create();

        $this->post(route('admin.login'), ['email' => $regularUser->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post(route('admin.login'), ['email' => $admin->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post(route('admin.login'), ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.leads.index'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_can_search_and_filter_contacts_without_rendering_html_from_leads(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->createLead('social/pasticcerie', 'Pasticceria <script>alert(1)</script>', 'claudio@example.com');
        $this->createLead('social/beauty', 'Salone Rossi', 'rossi@example.com');

        $this->actingAs($admin)->get(route('admin.leads.index', ['source' => 'social/pasticcerie', 'q' => 'claudio']))
            ->assertOk()
            ->assertSee('claudio@example.com')
            ->assertSee('Pasticceria &lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('rossi@example.com')
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertHeader('Cache-Control', 'max-age=0, no-store, private');
    }

    public function test_admin_contacts_are_paginated_and_invalid_source_is_rejected(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        foreach (range(1, 21) as $number) {
            $this->createLead('siti', 'Attività '.$number, 'lead'.$number.'@example.com');
        }

        $this->actingAs($admin)->get(route('admin.leads.index'))
            ->assertOk()
            ->assertSee('Pagina 1 di 2');

        $this->actingAs($admin)->get(route('admin.leads.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Pagina 2 di 2');

        $this->actingAs($admin)->get(route('admin.leads.index', ['source' => 'unknown']))
            ->assertSessionHasErrors('source');
    }

    public function test_admin_can_log_out(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->post(route('admin.logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_admin_create_command_creates_an_admin_with_a_hashed_password(): void
    {
        $this->artisan('admin:create')
            ->expectsQuestion('Nome', 'Mario Rossi')
            ->expectsQuestion('Email', 'admin@example.com')
            ->expectsQuestion('Password (almeno 12 caratteri)', 'password-lunga-123')
            ->expectsQuestion('Conferma password', 'password-lunga-123')
            ->assertExitCode(0);

        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $this->assertTrue($admin->is_admin);
        $this->assertNotSame('password-lunga-123', $admin->password);
    }

    public function test_admin_create_command_rejects_short_passwords(): void
    {
        $this->artisan('admin:create')
            ->expectsQuestion('Nome', 'Mario Rossi')
            ->expectsQuestion('Email', 'admin@example.com')
            ->expectsQuestion('Password (almeno 12 caratteri)', 'corta')
            ->expectsQuestion('Conferma password', 'corta')
            ->assertExitCode(1);

        $this->assertDatabaseCount('users', 0);
    }

    private function createLead(string $source, string $activity, string $email): Lead
    {
        return Lead::create([
            'nome' => 'Mario',
            'cognome' => 'Rossi',
            'email' => $email,
            'telefono' => '+39 320 000 0000',
            'attivita' => $activity,
            'settore' => 'Artigianato',
            'source' => $source,
            'consented_at' => now(),
        ]);
    }
}
