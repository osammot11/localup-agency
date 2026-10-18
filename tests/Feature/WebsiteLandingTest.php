<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebsiteLandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_website_landing_displays_offer_and_demo_form(): void
    {
        $this->get(route('sites.landing'))
            ->assertOk()
            ->assertSee('850 €')
            ->assertDontSee('850 € + IVA')
            ->assertSee('Aziende e attività che ci hanno scelto')
            ->assertSee('Richiedi la mia demo gratuita');
    }

    public function test_valid_demo_request_is_saved(): void
    {
        $this->post(route('sites.lead'), [
            'nome' => 'Mario',
            'cognome' => 'Rossi',
            'email' => 'mario@example.com',
            'telefono' => '+39 320 000 0000',
            'attivita' => 'Osteria del Podere',
            'settore' => 'Ristorazione',
            'sito_attuale' => 'www.osteriadelpodere.it',
            'consenso' => '1',
        ])->assertRedirect(route('sites.landing', ['success' => 1]).'#modulo');

        $this->assertDatabaseHas('leads', [
            'email' => 'mario@example.com',
            'sito_attuale' => 'www.osteriadelpodere.it',
            'source' => 'siti',
        ]);
    }

    public function test_demo_request_requires_consent(): void
    {
        $this->post(route('sites.lead'), [
            'nome' => 'Mario',
            'cognome' => 'Rossi',
            'email' => 'mario@example.com',
            'telefono' => '+39 320 000 0000',
            'attivita' => 'Osteria del Podere',
            'settore' => 'Ristorazione',
        ])->assertSessionHasErrors('consenso');

        $this->assertDatabaseCount('leads', 0);
    }
}
