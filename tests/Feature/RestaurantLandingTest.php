<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RestaurantLandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_restaurant_landing_displays_its_main_sections(): void
    {
        $this->get(route('restaurants.landing'))
            ->assertOk()
            ->assertSee('Sala piena')
            ->assertSee('Risultati veri, verificabili da te')
            ->assertSee('Compila il modulo e attiva il tuo mese gratuito');
    }

    public function test_valid_lead_is_saved_with_consent_and_source(): void
    {
        $this->post(route('restaurants.lead'), [
            'nome' => 'Mario',
            'cognome' => 'Rossi',
            'email' => 'mario@example.com',
            'telefono' => '+39 320 000 0000',
            'attivita' => 'Osteria del Podere',
            'settore' => 'Ristorazione',
            'consenso' => '1',
        ])->assertRedirect(route('restaurants.landing', ['success' => 1]).'#modulo');

        $this->assertDatabaseHas('leads', [
            'email' => 'mario@example.com',
            'source' => 'social/ristoranti',
        ]);
    }

    public function test_lead_requires_consent_and_rejects_honeypot(): void
    {
        $data = [
            'nome' => 'Mario',
            'cognome' => 'Rossi',
            'email' => 'mario@example.com',
            'telefono' => '+39 320 000 0000',
            'attivita' => 'Osteria del Podere',
            'settore' => 'Ristorazione',
        ];

        $this->post(route('restaurants.lead'), $data)->assertSessionHasErrors('consenso');
        $this->post(route('restaurants.lead'), $data + ['consenso' => '1', 'website' => 'spam'])
            ->assertSessionHasErrors('website');
        $this->assertDatabaseCount('leads', 0);
    }
}
