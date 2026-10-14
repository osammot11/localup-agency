<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLeadDetailTest extends TestCase
{
    use RefreshDatabase;

    private function createLead(string $email, string $source = 'social/beauty', ?string $website = null): Lead
    {
        return Lead::create([
            'nome' => 'Mario',
            'cognome' => 'Rossi',
            'email' => $email,
            'telefono' => '+39 320 000 0000',
            'attivita' => 'Salone Rossi',
            'settore' => 'Beauty',
            'sito_attuale' => $website,
            'source' => $source,
            'consented_at' => now(),
        ]);
    }

    public function test_only_admin_can_open_a_contact_detail(): void
    {
        $lead = $this->createLead('mario@example.com');

        $this->get(route('admin.leads.show', $lead))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())
            ->get(route('admin.leads.show', $lead))->assertForbidden();
        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('admin.leads.show', $lead))->assertOk();
    }

    public function test_admin_can_open_a_row_and_copy_email_phone_and_website(): void
    {
        $lead = $this->createLead('mario@example.com', website: 'https://salone.example.com');
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $this->get(route('admin.leads.index'))
            ->assertOk()
            ->assertSee('data-contact-url="'.route('admin.leads.show', ['lead' => $lead->id, 'page' => 1]).'"', false)
            ->assertSee('href="'.route('admin.leads.show', ['lead' => $lead->id, 'page' => 1]).'"', false);

        $this->get(route('admin.leads.show', $lead))
            ->assertOk()
            ->assertSee('mario@example.com')
            ->assertSee('+39 320 000 0000')
            ->assertSee('https://salone.example.com')
            ->assertSee('data-copy-target="contact-email"', false)
            ->assertSee('data-copy-target="contact-phone"', false)
            ->assertSee('data-copy-target="contact-website"', false)
            ->assertHeader('Cache-Control', 'max-age=0, no-store, private');
    }

    public function test_missing_website_has_no_copy_button_and_contact_data_is_escaped(): void
    {
        $lead = $this->createLead('mario@example.com', website: '<script>alert(1)</script>');
        $emptyWebsite = $this->createLead('other@example.com');
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $this->get(route('admin.leads.show', $lead))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
        $this->get(route('admin.leads.show', $emptyWebsite))
            ->assertOk()
            ->assertDontSee('data-copy-target="contact-website"', false);
    }

    public function test_arrows_follow_newest_first_order_and_preserve_list_filters(): void
    {
        $older = $this->createLead('match-old@example.com');
        $middle = $this->createLead('match-middle@example.com');
        $outside = $this->createLead('match-other@example.com', 'siti');
        $newer = $this->createLead('match-new@example.com');
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $context = ['q' => 'match', 'source' => 'social/beauty', 'page' => 1];

        $this->get(route('admin.leads.index', $context))
            ->assertSee(route('admin.leads.show', ['lead' => $middle->id] + $context));
        $this->get(route('admin.leads.show', ['lead' => $middle->id] + $context))
            ->assertOk()
            ->assertSee(route('admin.leads.show', ['lead' => $newer->id] + $context))
            ->assertSee(route('admin.leads.show', ['lead' => $older->id] + $context))
            ->assertSee(route('admin.leads.index', $context))
            ->assertDontSee(route('admin.leads.show', ['lead' => $outside->id] + $context));
        $this->get(route('admin.leads.show', ['lead' => $outside->id] + $context))->assertNotFound();
    }

    public function test_arrows_stop_at_first_and_last_contact(): void
    {
        $older = $this->createLead('old@example.com');
        $newer = $this->createLead('new@example.com');
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $this->get(route('admin.leads.show', $newer))
            ->assertOk()
            ->assertSee(route('admin.leads.show', $older))
            ->assertDontSee('aria-label="Contatto precedente, più recente"', false);
        $this->get(route('admin.leads.show', $older))
            ->assertOk()
            ->assertSee(route('admin.leads.show', $newer))
            ->assertDontSee('aria-label="Contatto successivo, meno recente"', false);
    }
}
