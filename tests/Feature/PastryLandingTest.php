<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PastryLandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_pastry_landing_displays_offer_and_proof(): void
    {
        $this->get(route('pastry.landing'))
            ->assertOk()
            ->assertSee('Più ordini di')
            ->assertSee('Pasticceria Claudio')
            ->assertSee('Domande frequenti sui social per pasticcerie');
    }

    public function test_valid_pastry_lead_is_saved_with_its_source(): void
    {
        $this->post(route('pastry.lead'), [
            'nome' => 'Mario',
            'cognome' => 'Rossi',
            'email' => 'mario@example.com',
            'telefono' => '+39 320 000 0000',
            'attivita' => 'Pasticceria Rossi',
            'settore' => 'Pasticceria',
            'consenso' => '1',
        ])->assertRedirect(route('pastry.landing', ['success' => 1]).'#modulo');

        $this->assertDatabaseHas('leads', [
            'email' => 'mario@example.com',
            'source' => 'social/pasticcerie',
        ]);
    }

    public function test_pastry_lead_requires_consent(): void
    {
        $this->post(route('pastry.lead'), [
            'nome' => 'Mario',
            'cognome' => 'Rossi',
            'email' => 'mario@example.com',
            'telefono' => '+39 320 000 0000',
            'attivita' => 'Pasticceria Rossi',
            'settore' => 'Pasticceria',
        ])->assertSessionHasErrors('consenso');

        $this->assertDatabaseCount('leads', 0);
    }
}
