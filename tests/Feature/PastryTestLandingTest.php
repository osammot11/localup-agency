<?php

namespace Tests\Feature;

use App\Mail\LeadReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PastryTestLandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_preview_has_two_forms_and_no_meta_pixel(): void
    {
        $response = $this->get(route('pastry-test.landing'));

        $response
            ->assertOk()
            ->assertViewIs('social.pastry-test')
            ->assertSee('noindex,nofollow,noarchive')
            ->assertSee('Il banco resta vuoto.')
            ->assertSee('Prima fornitori e stipendi.')
            ->assertDontSee('Toscana')
            ->assertDontSee('gelaterie')
            ->assertDontSee('panifici')
            ->assertDontSee('1.239.531')
            ->assertDontSee('2.988')
            ->assertSee('id="hero-trial-lead-form"', false)
            ->assertSee('id="trial-lead-form"', false)
            ->assertSee(route('pastry-test.lead'))
            ->assertDontSee('connect.facebook.net')
            ->assertDontSee('tailwind');

        preg_match_all('/data-sales-step="(\d+)"/', $response->getContent(), $matches);
        $this->assertSame(range(1, 17), array_map('intval', $matches[1]));
    }

    public function test_preview_submission_saves_a_separate_lead_without_notifications_or_meta_tracking(): void
    {
        config()->set('mail.lead_recipients', 'team@example.com');
        config()->set('services.meta.track_without_consent', true);
        config()->set('services.meta.access_token', 'test-token');
        Mail::fake();
        Http::fake();

        $this->post(route('pastry-test.lead'), [
            '_form_placement' => 'hero',
            'attivita' => 'Pasticceria di prova',
            'comune' => 'Lucca',
            'profilo_instagram' => '@prova',
            'nome' => 'Mario',
            'telefono' => '3331234567',
            'email' => 'mario@example.com',
            'consenso' => '1',
        ])->assertRedirect(route('pastry-test.landing', ['success' => 1]).'#modulo-hero');

        $this->assertDatabaseHas('leads', [
            'attivita' => 'Pasticceria di prova',
            'source' => 'social/pasticcerie-test',
            'settore' => 'Pasticceria',
        ]);
        $this->assertDatabaseCount('meta_events', 0);
        Mail::assertNotQueued(LeadReceived::class);
        Http::assertNothingSent();

        $this->get(route('pastry-test.landing', ['success' => 1]))
            ->assertOk()
            ->assertSee('Richiesta ricevuta');
    }
}
