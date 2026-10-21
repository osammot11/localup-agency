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

class FormStepTwoTrackingTest extends TestCase
{
    use RefreshDatabase;

    public static function landings(): array
    {
        return FormStartedTrackingTest::landings();
    }

    #[DataProvider('landings')]
    public function test_each_active_landing_sends_step_two_without_partial_form_values(string $landing, string $route, string $source): void
    {
        config()->set('services.meta.track_without_consent', true);
        config()->set('services.meta.access_token', 'test-token');
        Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1])]);
        $eventId = (string) Str::uuid();

        $this->get(route($route))->assertOk()->assertSee('localupMetaFormStep2Url');
        $this->withUnencryptedCookies([
            '_fbp' => 'fb.1.1791850000.123456789',
            '_fbc' => 'fb.1.1791850000.AbCdEfGh_123-XyZ',
        ]);
        $this->post(route('tracking.form-step-2'), [
            'event_id' => $eventId,
            'landing' => $landing,
            'attivita' => 'Non inviare a Meta',
        ])->assertNoContent();

        $this->assertDatabaseCount('leads', 0);
        $record = DB::table('meta_events')->sole();
        $this->assertSame($eventId, $record->id);
        $this->assertNull($record->lead_id);
        $this->assertSame('FormStep2Reached', $record->event_name);
        $this->assertSame('sent', $record->status);
        $payload = json_decode(Crypt::decryptString($record->payload), true);
        $this->assertSame('FormStep2Reached', $payload['event_name']);
        $this->assertSame($eventId, $payload['event_id']);
        $this->assertSame(route($route), $payload['event_source_url']);
        $this->assertSame($source, $payload['custom_data']['content_name']);
        $this->assertSame('fb.1.1791850000.123456789', $payload['user_data']['fbp']);
        $this->assertSame('fb.1.1791850000.AbCdEfGh_123-XyZ', $payload['user_data']['fbc']);
        $this->assertArrayNotHasKey('attivita', $payload);
        $this->assertArrayNotHasKey('em', $payload['user_data']);
        $this->assertArrayNotHasKey('ph', $payload['user_data']);
        Http::assertSent(fn ($request) => $request['data'][0]['event_name'] === 'FormStep2Reached'
            && $request['data'][0]['event_id'] === $eventId);
    }

    public function test_form_started_step_two_and_lead_are_separate_events(): void
    {
        config()->set('services.meta.track_without_consent', true);
        config()->set('services.meta.access_token', 'test-token');
        config()->set('mail.lead_recipients', '');
        Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1])]);
        $startedId = (string) Str::uuid();
        $stepTwoId = (string) Str::uuid();

        $this->post(route('tracking.form-start'), ['event_id' => $startedId, 'landing' => 'restaurants'])->assertNoContent();
        $this->post(route('tracking.form-step-2'), ['event_id' => $stepTwoId, 'landing' => 'restaurants'])->assertNoContent();
        $this->post(route('restaurants-trial.lead'), [
            'attivita' => 'Osteria',
            'comune' => 'Lucca',
            'nome' => 'Mario',
            'telefono' => '3200000000',
            'email' => 'mario@example.com',
            'consenso' => '1',
        ])->assertRedirect();

        $this->assertDatabaseCount('leads', 1);
        $this->assertDatabaseCount('meta_events', 3);
        $this->assertDatabaseHas('meta_events', ['id' => $startedId, 'event_name' => 'FormStarted', 'lead_id' => null]);
        $this->assertDatabaseHas('meta_events', ['id' => $stepTwoId, 'event_name' => 'FormStep2Reached', 'lead_id' => null]);
        $this->assertDatabaseHas('meta_events', ['event_name' => 'Lead', 'lead_id' => DB::table('leads')->value('id')]);
        Http::assertSentCount(3);
    }

    public function test_repeated_event_id_sends_step_two_only_once(): void
    {
        config()->set('services.meta.track_without_consent', true);
        config()->set('services.meta.access_token', 'test-token');
        Http::fake(['graph.facebook.com/*' => Http::response(['events_received' => 1])]);
        $data = ['event_id' => (string) Str::uuid(), 'landing' => 'restaurants'];

        $this->postJson(route('tracking.form-step-2'), $data)->assertNoContent();
        $this->postJson(route('tracking.form-step-2'), $data)->assertNoContent();

        $this->assertDatabaseCount('meta_events', 1);
        Http::assertSentCount(1);
    }

    public function test_invalid_or_disabled_step_two_requests_do_not_send(): void
    {
        config()->set('services.meta.track_without_consent', true);
        Http::fake();

        $this->postJson(route('tracking.form-step-2'), ['event_id' => 'bad', 'landing' => 'restaurants'])
            ->assertUnprocessable()->assertJsonValidationErrors('event_id');
        $this->postJson(route('tracking.form-step-2'), ['event_id' => (string) Str::uuid(), 'landing' => 'invalid'])
            ->assertUnprocessable()->assertJsonValidationErrors('landing');

        config()->set('services.meta.track_without_consent', false);
        $this->postJson(route('tracking.form-step-2'), ['event_id' => (string) Str::uuid(), 'landing' => 'restaurants'])
            ->assertNoContent();

        $this->assertDatabaseCount('meta_events', 0);
        Http::assertNothingSent();
    }

    public function test_granted_consent_and_capi_retry_apply_to_step_two(): void
    {
        config()->set('services.meta.track_without_consent', false);
        config()->set('services.meta.access_token', 'test-token');
        Http::fake(['graph.facebook.com/*' => Http::sequence()
            ->push(['error' => 'temporary'], 503)
            ->push(['events_received' => 1], 200)]);
        $this->post(route('tracking.consent'), ['choice' => 'granted', 'return_to' => '/social/ristoranti'])
            ->assertRedirect('/social/ristoranti');
        $consentId = (string) DB::table('marketing_consents')->value('id');
        $this->withCookie(MarketingConsent::COOKIE, $consentId);

        $this->post(route('tracking.form-step-2'), ['event_id' => (string) Str::uuid(), 'landing' => 'restaurants'])
            ->assertNoContent();
        $this->assertDatabaseHas('meta_events', ['event_name' => 'FormStep2Reached', 'consent_id' => $consentId, 'status' => 'pending', 'attempts' => 1]);

        $this->artisan('meta:retry')->assertSuccessful();

        $this->assertDatabaseHas('meta_events', ['event_name' => 'FormStep2Reached', 'status' => 'sent', 'attempts' => 2]);
        Http::assertSentCount(2);
    }
}
