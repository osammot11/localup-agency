<?php

namespace App\Services;

use App\Models\Lead;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MetaConversionsApi
{
    public function captureLead(Lead $lead, Request $request, ?string $consentId): string
    {
        $eventId = (string) Str::uuid();

        $userData = array_filter([
            'em' => $this->hash(mb_strtolower(trim($lead->email))),
            'ph' => $this->hash($this->normalizePhone($lead->telefono)),
            'fn' => $this->hash(mb_strtolower(trim($lead->nome))),
            'ln' => $this->hash(mb_strtolower(trim($lead->cognome))),
            'client_ip_address' => $request->ip(),
            'client_user_agent' => $request->userAgent(),
            'fbp' => $this->metaCookie($request, '_fbp'),
            'fbc' => $this->metaCookie($request, '_fbc'),
        ], fn (mixed $value): bool => $value !== null && $value !== '');

        $event = [
            'event_name' => 'Lead',
            'event_time' => $lead->created_at->timestamp,
            'event_id' => $eventId,
            'action_source' => 'website',
            'event_source_url' => $request->url(),
            'user_data' => $userData,
            'custom_data' => ['content_name' => $lead->source],
        ];

        DB::table('meta_events')->insert([
            'id' => $eventId,
            'lead_id' => $lead->id,
            'consent_id' => $consentId,
            'event_name' => 'Lead',
            'payload' => Crypt::encryptString(json_encode($event, JSON_THROW_ON_ERROR)),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->deliver($eventId);

        return $eventId;
    }

    public function captureFormProgress(string $eventName, string $eventId, string $source, string $sourceUrl, Request $request, ?string $consentId): void
    {
        $userData = array_filter([
            'client_ip_address' => $request->ip(),
            'client_user_agent' => $request->userAgent(),
            'fbp' => $this->metaCookie($request, '_fbp'),
            'fbc' => $this->metaCookie($request, '_fbc'),
        ], fn (mixed $value): bool => $value !== null && $value !== '');

        $event = [
            'event_name' => $eventName,
            'event_time' => now()->timestamp,
            'event_id' => $eventId,
            'action_source' => 'website',
            'event_source_url' => $sourceUrl,
            'user_data' => $userData,
            'custom_data' => ['content_name' => $source],
        ];

        $inserted = DB::table('meta_events')->insertOrIgnore([
            'id' => $eventId,
            'lead_id' => null,
            'consent_id' => $consentId,
            'event_name' => $eventName,
            'payload' => Crypt::encryptString(json_encode($event, JSON_THROW_ON_ERROR)),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($inserted === 1) {
            $this->deliver($eventId);
        }
    }

    public function deliver(string $eventId): void
    {
        $record = DB::table('meta_events')->where('id', $eventId)->first();

        if ($record === null || $record->status !== 'pending') {
            return;
        }

        if ($record->created_at < now()->subDay()->toDateTimeString()) {
            $this->finish($eventId, 'expired');

            return;
        }

        $trackingAllowed = $record->consent_id === null
            ? config('services.meta.track_without_consent') === true
            : DB::table('marketing_consents')
                ->where('id', $record->consent_id)
                ->where('status', 'granted')
                ->exists();

        if (! $trackingAllowed) {
            $this->finish($eventId, 'cancelled');

            return;
        }

        $pixelId = (string) config('services.meta.pixel_id');
        $token = (string) config('services.meta.access_token');
        $version = (string) config('services.meta.graph_version');

        if (! preg_match('/^\d+$/', $pixelId) || $token === '' || ! preg_match('/^v\d+\.\d+$/', $version)) {
            return;
        }

        $payload = ['data' => [json_decode(Crypt::decryptString($record->payload), true, flags: JSON_THROW_ON_ERROR)]];
        $testCode = config('services.meta.test_event_code');

        if (is_string($testCode) && $testCode !== '') {
            $payload['test_event_code'] = $testCode;
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->connectTimeout(2)
                ->timeout(4)
                ->post("https://graph.facebook.com/{$version}/{$pixelId}/events", $payload);

            $status = $response->status();
            $received = $response->json('events_received');

            if ($response->successful() && $received === 1) {
                $this->finish($eventId, 'sent', $status);

                return;
            }

            $this->finish($eventId, $status === 429 || $status >= 500 ? 'pending' : 'failed', $status);
            Log::warning('Meta CAPI event not accepted', ['event_id' => $eventId, 'http_status' => $status]);
        } catch (ConnectionException $exception) {
            $this->finish($eventId, 'pending');
            Log::warning('Meta CAPI connection failed', ['event_id' => $eventId]);
        }
    }

    private function finish(string $eventId, string $status, ?int $httpStatus = null): void
    {
        DB::table('meta_events')->where('id', $eventId)->update([
            'status' => $status,
            'attempts' => DB::raw('attempts + 1'),
            'last_http_status' => $httpStatus,
            'last_attempt_at' => now(),
            'sent_at' => $status === 'sent' ? now() : null,
            'updated_at' => now(),
        ]);
    }

    private function hash(string $value): ?string
    {
        return $value === '' ? null : hash('sha256', $value);
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        return strlen($digits) === 10 ? '39'.$digits : $digits;
    }

    private function metaCookie(Request $request, string $name): ?string
    {
        $value = $request->cookie($name);

        return is_string($value) && strlen($value) <= 255 && preg_match('/^fb\.1\.\d+\.[A-Za-z0-9_\-]+$/', $value) ? $value : null;
    }
}
