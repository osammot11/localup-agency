<?php

namespace App\Services;

use App\Mail\LeadReceived;
use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

class LandingLeadService
{
    public function submit(Request $request, array $data, string $source, string $landingRoute): RedirectResponse
    {
        unset($data['website'], $data['consenso']);

        $lead = Lead::create($data + ['source' => $source, 'consented_at' => now()]);
        $this->queueLeadEmails($lead);
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

    private function queueLeadEmails(Lead $lead): void
    {
        $recipients = collect(explode(',', (string) config('mail.lead_recipients')))
            ->map(fn (string $recipient): string => trim($recipient))
            ->filter()
            ->unique(fn (string $recipient): string => strtolower($recipient));

        foreach ($recipients as $recipient) {
            if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                report(new \InvalidArgumentException('Indirizzo email non valido in LEAD_NOTIFICATION_EMAILS: '.$recipient));

                continue;
            }

            try {
                Mail::to($recipient)->queue(new LeadReceived($lead));
            } catch (Throwable $exception) {
                report($exception);
            }
        }
    }
}
