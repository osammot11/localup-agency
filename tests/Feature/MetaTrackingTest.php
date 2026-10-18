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
        config()->set('services.meta.track_without_consent', false);

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
        config()->set('services.meta.track_without_consent', false);

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
        config()->set('services.meta.track_without_consent', false);
        Http::fake();
        $this->post(route('restaurants.lead'), $this->lead())->assertRedirect();
        $this->assertDatabaseCount('leads', 1);
        $this->assertDatabaseCount('meta_events', 0);
        Http::assertNothingSent();
    }

    public function test_pixel_and_capi_use_same_lead_event_id_after_marketing_consent(): void
    {
        config()->set('services.meta.track_without_consent', false);
        config()->set('services.meta.access_token', 'test-token');
        Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1])]);
        $consentId = $this->consent();

        $this->get(route('restaurants.landing'))
            ->assertSee('connect.facebook.net')
            ->assertSee('1612527090508674');

        $this->withUnencryptedCookies([
            '_fbp' => 'fb.1.1791850000.123456789',
            '_fbc' => 'fb.1.1791850000.AbCdEfGh_123-XyZ',
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
        $this->assertSame('fb.1.1791850000.AbCdEfGh_123-XyZ', $payload['user_data']['fbc']);
        $this->get(route('restaurants.landing', ['success' => 1]))
            ->assertSee('eventID: "'.$event->id.'"', false);
        Http::assertSent(fn ($request) => $request->url() === 'https://graph.facebook.com/v26.0/1612527090508674/events'
            && $request->hasHeader('Authorization', 'Bearer test-token')
            && $request['data'][0]['event_id'] === $event->id);
    }

    public function test_revocation_cancels_pending_events_and_stops_pixel(): void
    {
        config()->set('services.meta.track_without_consent', false);
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
        config()->set('services.meta.track_without_consent', false);
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

    public function test_every_landing_and_homepage_load_pixel_without_cookie_banner_in_experiment_mode(): void
    {
        config()->set('services.meta.track_without_consent', true);

        foreach (['/', route('restaurants.landing'), route('sites.landing'), route('beauty.landing'), route('antiques.landing'), route('pastry.landing')] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('connect.facebook.net')
                ->assertSee("fbq('track', 'PageView')", false)
                ->assertDontSee('Cookie e tracciamento')
                ->assertDontSee('Preferenze cookie');
        }
    }

    public function test_every_landing_sends_pixel_and_capi_lead_without_cookie_consent(): void
    {
        config()->set('services.meta.track_without_consent', true);
        config()->set('services.meta.access_token', 'test-token');
        Http::preventStrayRequests();
        Http::fake(['https://graph.facebook.com/v26.0/1612527090508674/events' => Http::response(['events_received' => 1])]);
        $this->withUnencryptedCookies([
            '_fbp' => 'fb.1.1791850000.123456789',
            '_fbc' => 'fb.1.1791850000.AbCdEfGh_123-XyZ',
        ]);

        foreach (['restaurants', 'sites', 'beauty', 'antiques', 'pastry'] as $name) {
            $this->post(route($name.'.lead'), $this->lead())
                ->assertRedirect(route($name.'.landing', ['success' => 1]).'#modulo')
                ->assertSessionHas('meta_lead_event_id');

            $event = DB::table('meta_events')->where('lead_id', DB::table('leads')->max('id'))->first();
            $this->assertNull($event->consent_id);
            $this->assertSame('sent', $event->status);
            $this->get(route($name.'.landing', ['success' => 1]))
                ->assertOk()
                ->assertSee('eventID: "'.$event->id.'"', false);
            Http::assertSent(fn ($request) => $request['data'][0]['event_id'] === $event->id
                && $request['data'][0]['user_data']['fbc'] === 'fb.1.1791850000.AbCdEfGh_123-XyZ');
        }

        $this->assertDatabaseCount('leads', 5);
        $this->assertDatabaseCount('meta_events', 5);
        Http::assertSentCount(5);
    }

    public function test_disabling_experiment_mode_cancels_pending_event_without_consent(): void
    {
        config()->set('services.meta.track_without_consent', true);
        config()->set('services.meta.access_token', '');
        Http::preventStrayRequests();
        Http::fake(['https://graph.facebook.com/v26.0/1612527090508674/events' => Http::response(['events_received' => 1])]);
        $this->post(route('restaurants.lead'), $this->lead())->assertRedirect();
        $this->assertDatabaseHas('meta_events', ['consent_id' => null, 'status' => 'pending']);

        config()->set('services.meta.track_without_consent', false);
        config()->set('services.meta.access_token', 'test-token');
        $this->artisan('meta:retry')->assertSuccessful();

        $this->assertDatabaseHas('meta_events', ['consent_id' => null, 'status' => 'cancelled']);
        Http::assertNothingSent();
    }

    public function test_previous_cookie_rejection_does_not_block_experiment_tracking(): void
    {
        config()->set('services.meta.track_without_consent', true);
        config()->set('services.meta.access_token', 'test-token');
        Http::preventStrayRequests();
        Http::fake(['https://graph.facebook.com/v26.0/1612527090508674/events' => Http::response(['events_received' => 1])]);
        $this->post(route('tracking.consent'), ['choice' => 'rejected', 'return_to' => '/social/ristoranti'])
            ->assertRedirect('/social/ristoranti');
        $this->withCookie(MarketingConsent::COOKIE, (string) DB::table('marketing_consents')->value('id'));

        $this->get(route('restaurants.landing'))->assertOk()->assertSee('connect.facebook.net');
        $this->post(route('restaurants.lead'), $this->lead())
            ->assertRedirect(route('restaurants.landing', ['success' => 1]).'#modulo')
            ->assertSessionHas('meta_lead_event_id');

        $this->assertDatabaseHas('meta_events', ['consent_id' => null, 'status' => 'sent']);
        Http::assertSentCount(1);
    }
}
