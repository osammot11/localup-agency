<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AntiquesLandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_antiques_landing_displays_offer_and_proof(): void
    {
        $this->get(route('antiques.landing'))
            ->assertOk()
            ->assertSee('I tuoi pezzi davanti ai')
            ->assertSee('Lumart')
            ->assertSee('Domande frequenti sul marketing per antiquari');
    }

    public function test_valid_antiques_lead_is_saved_with_its_source(): void
    {
        $this->post(route('antiques.lead'), [
            'nome' => 'Mario',
            'cognome' => 'Rossi',
            'email' => 'mario@example.com',
            'telefono' => '+39 320 000 0000',
            'attivita' => 'Galleria Rossi',
            'settore' => 'Antiquariato',
            'consenso' => '1',
        ])->assertRedirect(route('antiques.landing', ['success' => 1]).'#modulo');

        $this->assertDatabaseHas('leads', [
            'email' => 'mario@example.com',
            'source' => 'social/antiquariato',
        ]);
    }

    public function test_antiques_lead_requires_consent(): void
    {
        $this->post(route('antiques.lead'), [
            'nome' => 'Mario',
            'cognome' => 'Rossi',
            'email' => 'mario@example.com',
            'telefono' => '+39 320 000 0000',
            'attivita' => 'Galleria Rossi',
            'settore' => 'Antiquariato',
        ])->assertSessionHasErrors('consenso');

        $this->assertDatabaseCount('leads', 0);
    }
}
