<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BeautyLandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_beauty_landing_displays_offer_and_proof(): void
    {
        $this->get(route('beauty.landing'))
            ->assertOk()
            ->assertSee('Agenda piena tutta la settimana')
            ->assertSee('Piegaia Parrucchieri')
            ->assertSee('Domande frequenti sul social media marketing per saloni');
    }

    public function test_valid_beauty_lead_is_saved_with_its_source(): void
    {
        $this->post(route('beauty.lead'), [
            'nome' => 'Maria',
            'cognome' => 'Rossi',
            'email' => 'maria@example.com',
            'telefono' => '+39 320 000 0000',
            'attivita' => 'Salone Maria',
            'settore' => 'Parrucchiera',
            'consenso' => '1',
        ])->assertRedirect(route('beauty.landing', ['success' => 1]).'#modulo');

        $this->assertDatabaseHas('leads', [
            'email' => 'maria@example.com',
            'source' => 'social/beauty',
        ]);
    }

    public function test_beauty_lead_requires_consent(): void
    {
        $this->post(route('beauty.lead'), [
            'nome' => 'Maria',
            'cognome' => 'Rossi',
            'email' => 'maria@example.com',
            'telefono' => '+39 320 000 0000',
            'attivita' => 'Salone Maria',
            'settore' => 'Parrucchiera',
        ])->assertSessionHasErrors('consenso');

        $this->assertDatabaseCount('leads', 0);
    }
}
