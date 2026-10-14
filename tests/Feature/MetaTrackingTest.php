<?php

namespace Tests\Feature;

use App\Services\MarketingConsent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MetaTrackingTest extends TestCase
{
    use RefreshDatabase;

    private function lead(): array
    {
        return [
            'nome' => 'Mario',
            'cognome' => 'Rossi',
            'email' => 'mario@example.com',
            'telefono' => '+39 320 000 0000',
            'attivita' => 'Osteria',
            'settore' => 'Ristorazione',
            'consenso' => '1',
        ];
    }

    private function consent(): string
    {
        $response = $this->post(route('tracking.consent'), [
            'choice' => 'granted',
            'return_to' => '/social/ristoranti',
        ]);
        $response->assertRedirect('/social/ristoranti');
        $id = (string) DB::table('marketing_consents')->value('id');
        $this->withCookie(MarketingConsent::COOKIE, $id);

        return $id;
    }

    public function test_every_landing_waits_for_marketing_consent(): void
    {
        foreach (['restaurants', 'sites', 'beauty', 'antiques', 'pastry'] as $name) {
            $this->get(route($name.'.landing'))
                ->assertOk()
                ->assertSee('Cookie e tracciamento')
                ->assertSee('Preferenze cookie')
                ->assertDontSee('connect.facebook.net');
        }
    }

    public function test_homepage_shows_all_five_paths_and_accepts_cookie_choice(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('La tua attività merita di')
            ->assertSee(route('sites.landing'))
            ->assertSee(route('restaurants.landing'))
            ->assertSee(route('beauty.landing'))
            ->assertSee(route('antiques.landing'))
            ->assertSee(route('pastry.landing'))
            ->assertSee('Cookie e tracciamento')
            ->assertDontSee('connect.facebook.net');

        $this->post(route('tracking.consent'), ['choice' => 'granted', 'return_to' => '/'])
            ->assertRedirect('/');

        $this->withCookie(MarketingConsent::COOKIE, (string) DB::table('marketing_consents')->value('id'));
        $this->get('/')->assertOk()->assertSee('connect.facebook.net');
    }

    public function test_form_consent_alone_does_not_send_meta_events(): void
    {
        Http::fake();
        $this->post(route('restaurants.lead'), $this->lead())->assertRedirect();
        $this->assertDatabaseCount('leads', 1);
        $this->assertDatabaseCount('meta_events', 0);
        Http::assertNothingSent();
    }

    public function test_pixel_and_capi_use_same_lead_event_id_after_marketing_consent(): void
    {
        config()->set('services.meta.access_token', 'test-token');
        Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1])]);
        $consentId = $this->consent();

        $this->get(route('restaurants.landing'))
            ->assertSee('connect.facebook.net')
            ->assertSee('1612527090508674');

        $this->withUnencryptedCookies([
            '_fbp' => 'fb.1.1791850000.123456789',
            '_fbc' => 'fb.1.1791850000.testclickid',
        ]);
        $this->post(route('restaurants.lead'), $this->lead())
            ->assertRedirect(route('restaurants.landing', ['success' => 1]).'#modulo')
            ->assertSessionHas('meta_lead_event_id');

        $event = DB::table('meta_events')->first();
        $this->assertSame('sent', $event->status);
        $this->assertSame($consentId, $event->consent_id);
        $this->assertSame(1, $event->attempts);
        $this->assertStringNotContainsString('mario@example.com', $event->payload);
        $payload = json_decode(Crypt::decryptString($event->payload), true);
        $this->assertSame($event->id, $payload['event_id']);
        $this->assertSame(hash('sha256', 'mario@example.com'), $payload['user_data']['em']);
        $this->assertSame('fb.1.1791850000.123456789', $payload['user_data']['fbp']);
        $this->assertSame('fb.1.1791850000.testclickid', $payload['user_data']['fbc']);
        $this->get(route('restaurants.landing', ['success' => 1]))
            ->assertSee('eventID: "'.$event->id.'"', false);
        Http::assertSent(fn ($request) => $request->url() === 'https://graph.facebook.com/v26.0/1612527090508674/events'
            && $request->hasHeader('Authorization', 'Bearer test-token')
            && $request['data'][0]['event_id'] === $event->id);
    }

    public function test_revocation_cancels_pending_events_and_stops_pixel(): void
    {
        config()->set('services.meta.access_token', '');
        $this->consent();
        $this->post(route('restaurants.lead'), $this->lead())->assertRedirect();
        $this->assertDatabaseHas('meta_events', ['status' => 'pending']);

        $response = $this->post(route('tracking.consent'), [
            'choice' => 'rejected',
            'return_to' => '/social/ristoranti',
        ]);
        $this->assertDatabaseHas('meta_events', ['status' => 'cancelled']);
        $this->withCookie(MarketingConsent::COOKIE, (string) DB::table('marketing_consents')->value('id'));
        $this->get(route('restaurants.landing'))->assertDontSee('connect.facebook.net');
        $this->post(route('restaurants.lead'), $this->lead())->assertRedirect();
        $this->assertDatabaseCount('meta_events', 1);
    }

    public function test_retry_sends_transient_failure_without_losing_lead(): void
    {
        config()->set('services.meta.access_token', 'test-token');
        Http::fake(['graph.facebook.com/*' => Http::sequence()
            ->push(['error' => 'temporary'], 503)
            ->push(['events_received' => 1], 200)]);
        $this->consent();
        $this->post(route('restaurants.lead'), $this->lead())->assertRedirect();
        $this->assertDatabaseCount('leads', 1);
        $this->assertDatabaseHas('meta_events', ['status' => 'pending', 'attempts' => 1]);
        $this->artisan('meta:retry')->assertSuccessful();
        $this->assertDatabaseHas('meta_events', ['status' => 'sent', 'attempts' => 2]);
    }
}
