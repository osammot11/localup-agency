<?php

namespace App\Services;

use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class LandingLeadService
{
    public function submit(Request $request, array $data, string $source, string $landingRoute): RedirectResponse
    {
        unset($data['website'], $data['consenso']);

        $lead = Lead::create($data + ['source' => $source, 'consented_at' => now()]);
        $redirect = redirect(route($landingRoute, ['success' => 1]).'#modulo');
        $consentId = app(MarketingConsent::class)->grantedId($request);

        if ($consentId === null || ! config('services.meta.pixel_id')) {
            return $redirect;
        }

        try {
            $eventId = app(MetaConversionsApi::class)->captureLead($lead, $request, $consentId);

            $redirect->with('meta_lead_event_id', $eventId);
        } catch (Throwable $exception) {
            report($exception);
        }

        return $redirect;
    }
}
