<?php

namespace Tests\Feature;

use App\Services\MarketingConsent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FormStartedTrackingTest extends TestCase
{
    use RefreshDatabase;

    public static function landings(): array
    {
        return [
            'restaurants' => ['restaurants', 'restaurants-trial.landing', 'social/ristoranti-v2'],
            'beauty' => ['beauty', 'beauty-trial.landing', 'social/beauty-v2'],
            'antiques' => ['antiques', 'antiques-trial.landing', 'social/antiquariato-v2'],
            'pastry' => ['pastry', 'pastry-trial.landing', 'social/pasticcerie-v2'],
            'sites' => ['sites', 'sites-trial.landing', 'siti-v2'],
        ];
    }

    #[DataProvider('landings')]
    public function test_each_active_landing_sends_form_started_without_a_lead_or_form_values(string $landing, string $route, string $source): void
    {
        config()->set('services.meta.track_without_consent', true);
        config()->set('services.meta.access_token', 'test-token');
        Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1])]);
        $eventId = (string) Str::uuid();

        $this->get(route($route))
            ->assertOk()
            ->assertSee('localupMetaFormStartUrl');

        $this->withUnencryptedCookies([
            '_fbp' => 'fb.1.1791850000.123456789',
            '_fbc' => 'fb.1.1791850000.AbCdEfGh_123-XyZ',
        ]);
        $this->post(route('tracking.form-start'), ['event_id' => $eventId, 'landing' => $landing, 'nome' => 'Do not send'])
            ->assertNoContent();

        $this->assertDatabaseCount('leads', 0);
        $record = DB::table('meta_events')->sole();
        $this->assertSame($eventId, $record->id);
        $this->assertNull($record->lead_id);
        $this->assertSame('FormStarted', $record->event_name);
        $this->assertSame('sent', $record->status);
        $payload = json_decode(Crypt::decryptString($record->payload), true);
        $this->assertSame('FormStarted', $payload['event_name']);
        $this->assertSame($eventId, $payload['event_id']);
        $this->assertSame(route($route), $payload['event_source_url']);
        $this->assertSame($source, $payload['custom_data']['content_name']);
        $this->assertSame('fb.1.1791850000.123456789', $payload['user_data']['fbp']);
        $this->assertSame('fb.1.1791850000.AbCdEfGh_123-XyZ', $payload['user_data']['fbc']);
        $this->assertArrayNotHasKey('nome', $payload);
        $this->assertArrayNotHasKey('em', $payload['user_data']);
        $this->assertArrayNotHasKey('ph', $payload['user_data']);
        Http::assertSent(fn ($request) => $request['data'][0]['event_name'] === 'FormStarted'
            && $request['data'][0]['event_id'] === $eventId);
    }

    public function test_repeated_event_id_sends_capi_once_and_does_not_create_a_lead(): void
    {
        config()->set('services.meta.track_without_consent', true);
        config()->set('services.meta.access_token', 'test-token');
        Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1])]);
        $data = ['event_id' => (string) Str::uuid(), 'landing' => 'restaurants'];

        $this->postJson(route('tracking.form-start'), $data)->assertNoContent();
        $this->postJson(route('tracking.form-start'), $data)->assertNoContent();

        $this->assertDatabaseCount('meta_events', 1);
        $this->assertDatabaseCount('leads', 0);
        Http::assertSentCount(1);
    }

    public function test_form_start_and_successful_submission_remain_separate_events(): void
    {
        config()->set('services.meta.track_without_consent', true);
        config()->set('services.meta.access_token', 'test-token');
        config()->set('mail.lead_recipients', '');
        Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1])]);
        $formStartedId = (string) Str::uuid();

        $this->post(route('tracking.form-start'), ['event_id' => $formStartedId, 'landing' => 'restaurants'])
            ->assertNoContent();
        $this->post(route('restaurants-trial.lead'), [
            'attivita' => 'Osteria',
            'comune' => 'Lucca',
            'nome' => 'Mario',
            'telefono' => '3200000000',
            'email' => 'mario@example.com',
            'consenso' => '1',
        ])->assertRedirect(route('restaurants-trial.landing', ['success' => 1]).'#modulo');

        $this->assertDatabaseCount('leads', 1);
        $this->assertDatabaseCount('meta_events', 2);
        $this->assertDatabaseHas('meta_events', ['id' => $formStartedId, 'event_name' => 'FormStarted', 'lead_id' => null]);
        $this->assertDatabaseHas('meta_events', ['event_name' => 'Lead', 'lead_id' => DB::table('leads')->value('id')]);
        Http::assertSentCount(2);
    }

    public function test_invalid_event_id_or_landing_is_rejected(): void
    {
        config()->set('services.meta.track_without_consent', true);
        Http::fake();

        $this->postJson(route('tracking.form-start'), ['event_id' => 'invalid', 'landing' => 'restaurants'])
            ->assertUnprocessable()->assertJsonValidationErrors('event_id');
        $this->postJson(route('tracking.form-start'), ['event_id' => (string) Str::uuid(), 'landing' => 'unknown'])
            ->assertUnprocessable()->assertJsonValidationErrors('landing');

        $this->assertDatabaseCount('meta_events', 0);
        Http::assertNothingSent();
    }

    public function test_tracking_disabled_without_consent_does_not_record_form_start(): void
    {
        config()->set('services.meta.track_without_consent', false);
        Http::fake();

        $this->postJson(route('tracking.form-start'), ['event_id' => (string) Str::uuid(), 'landing' => 'restaurants'])
            ->assertNoContent();

        $this->assertDatabaseCount('meta_events', 0);
        Http::assertNothingSent();
    }

    public function test_granted_marketing_consent_allows_form_start_in_consent_mode(): void
    {
        config()->set('services.meta.track_without_consent', false);
        config()->set('services.meta.access_token', 'test-token');
        Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1])]);
        $this->post(route('tracking.consent'), ['choice' => 'granted', 'return_to' => '/social/ristoranti'])
            ->assertRedirect('/social/ristoranti');
        $consentId = (string) DB::table('marketing_consents')->value('id');
        $this->withCookie(MarketingConsent::COOKIE, $consentId);

        $this->post(route('tracking.form-start'), ['event_id' => (string) Str::uuid(), 'landing' => 'restaurants'])
            ->assertNoContent();

        $this->assertDatabaseHas('meta_events', ['event_name' => 'FormStarted', 'consent_id' => $consentId, 'status' => 'sent']);
        Http::assertSentCount(1);
    }

    public function test_active_forms_load_the_shared_tracker_and_legacy_urls_do_not(): void
    {
        config()->set('services.meta.track_without_consent', true);

        foreach (self::landings() as [$landing, $activeRoute]) {
            $response = $this->get(route($activeRoute))->assertOk()
                ->assertSee('/js/restaurants-trial.js');

            $this->assertStringContainsString('data-form-placement="hero"', $response->getContent());
            if (in_array($landing, ['sites', 'antiques', 'beauty'], true)) {
                $this->assertStringContainsString('data-form-placement="bottom"', $response->getContent());
            }

            $this->get(route($landing.'.landing'))->assertOk()
                ->assertDontSee('/js/restaurants-trial.js');
        }
    }

    public function test_transient_capi_failure_is_retried_without_creating_a_lead(): void
    {
        config()->set('services.meta.track_without_consent', true);
        config()->set('services.meta.access_token', 'test-token');
        Http::fake(['graph.facebook.com/*' => Http::sequence()
            ->push(['error' => 'temporary'], 503)
            ->push(['events_received' => 1], 200)]);

        $this->postJson(route('tracking.form-start'), ['event_id' => (string) Str::uuid(), 'landing' => 'restaurants'])
            ->assertNoContent();
        $this->assertDatabaseHas('meta_events', ['event_name' => 'FormStarted', 'status' => 'pending', 'attempts' => 1]);

        $this->artisan('meta:retry')->assertSuccessful();

        $this->assertDatabaseHas('meta_events', ['event_name' => 'FormStarted', 'status' => 'sent', 'attempts' => 2]);
        $this->assertDatabaseCount('leads', 0);
        Http::assertSentCount(2);
    }
}
