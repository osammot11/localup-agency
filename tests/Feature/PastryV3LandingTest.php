<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PastryV3LandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_v3_landing_follows_the_sales_skeleton_and_posts_to_its_own_route(): void
    {
        $response = $this->get('/social/pasticcerie-v3');

        $response
            ->assertOk()
            ->assertViewIs('social.pastry-v3')
            ->assertSee('Metodo Appena Sfornato')
            ->assertSee('1.239.531 visualizzazioni')
            ->assertSee('id="hero-trial-lead-form"', false)
            ->assertSee('id="trial-lead-form"', false)
            ->assertSee('action="'.route('pastry-v3.lead').'"', false)
            ->assertDontSee('action="'.route('pastry-trial.lead').'"', false);

        preg_match_all('/data-sales-step="(\d+)"/', $response->getContent(), $matches);
        $this->assertSame(range(1, 17), array_map('intval', $matches[1]));
    }

    public function test_v3_submission_saves_lead_with_its_own_source(): void
    {
        $this->post(route('pastry-v3.lead'), [
            '_form_placement' => 'bottom',
            'attivita' => 'Pasticceria Rossi',
            'comune' => 'Lucca',
            'profilo_instagram' => '@rossi',
            'nome' => 'Anna',
            'telefono' => '3331234567',
            'email' => 'anna@example.com',
            'consenso' => '1',
        ])->assertRedirect(route('pastry-v3.landing', ['success' => 1]).'#modulo');

        $this->assertDatabaseHas('leads', [
            'attivita' => 'Pasticceria Rossi',
            'source' => 'social/pasticcerie-v3',
            'settore' => 'Pasticceria',
        ]);
    }

    public function test_form_progress_tracking_accepts_the_v3_landing(): void
    {
        $this->postJson(route('tracking.form-start'), [
            'event_id' => '8f14e45f-ceea-467f-a8f3-9b1c2d3e4f50',
            'landing' => 'pastry_v3',
        ])->assertNoContent();
    }
}
