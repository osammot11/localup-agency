<?php

namespace App\Http\Controllers;

use App\Services\MarketingConsent;
use App\Services\MetaConversionsApi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Throwable;

class FormProgressTrackingController extends Controller
{
    private const LANDINGS = [
        'restaurants' => ['route' => 'restaurants-trial.landing', 'source' => 'social/ristoranti-v2'],
        'beauty' => ['route' => 'beauty-trial.landing', 'source' => 'social/beauty-v2'],
        'antiques' => ['route' => 'antiques-trial.landing', 'source' => 'social/antiquariato-v2'],
        'pastry' => ['route' => 'pastry-trial.landing', 'source' => 'social/pasticcerie-v2'],
        'sites' => ['route' => 'sites-trial.landing', 'source' => 'siti-v2'],
    ];

    public function started(Request $request, MetaConversionsApi $meta, MarketingConsent $consent): Response
    {
        return $this->capture('FormStarted', $request, $meta, $consent);
    }

    public function stepTwo(Request $request, MetaConversionsApi $meta, MarketingConsent $consent): Response
    {
        return $this->capture('FormStep2Reached', $request, $meta, $consent);
    }

    private function capture(string $eventName, Request $request, MetaConversionsApi $meta, MarketingConsent $consent): Response
    {
        $data = $request->validate([
            'event_id' => ['required', 'uuid'],
            'landing' => ['required', Rule::in(array_keys(self::LANDINGS))],
        ]);

        $pixelId = config('services.meta.pixel_id');
        $trackWithoutConsent = config('services.meta.track_without_consent') === true;
        $consentId = $trackWithoutConsent ? null : $consent->grantedId($request);

        if (! is_string($pixelId) || ! preg_match('/^\d+$/', $pixelId) || (! $trackWithoutConsent && $consentId === null)) {
            return response()->noContent();
        }

        $landing = self::LANDINGS[$data['landing']];

        try {
            $meta->captureFormProgress($eventName, $data['event_id'], $landing['source'], route($landing['route']), $request, $consentId);
        } catch (Throwable $exception) {
            report($exception);

            return response('', 503);
        }

        return response()->noContent();
    }
}
