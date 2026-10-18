<?php

namespace App\Http\Controllers;

use App\Services\LandingLeadService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TrialLandingController extends Controller
{
    public function show(Request $request): View
    {
        return view('social.trial-variant', ['landing' => $this->landing($request)]);
    }

    public function store(Request $request, LandingLeadService $leads): RedirectResponse
    {
        $landing = $this->landing($request);
        $isWebsite = $landing['key'] === 'sites';

        $data = $request->validate([
            'website' => ['nullable', 'max:0'],
            'attivita' => ['required', 'string', 'max:150'],
            'comune' => ['required', 'string', 'max:100'],
            'profilo_instagram' => $isWebsite ? ['prohibited'] : ['nullable', 'string', 'max:255'],
            'sito_attuale' => $isWebsite ? ['nullable', 'string', 'max:255'] : ['prohibited'],
            'settore' => $isWebsite ? ['required', 'string', 'max:150'] : ['prohibited'],
            'nome' => ['required', 'string', 'max:100'],
            'cognome' => $isWebsite ? ['required', 'string', 'max:100'] : ['prohibited'],
            'telefono' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255'],
            'consenso' => ['accepted'],
        ]);

        return $leads->submit(
            $request,
            $data + ['cognome' => '', 'settore' => $landing['sector']],
            $landing['source'],
            $landing['route'].'.landing',
        );
    }

    private function landing(Request $request): array
    {
        return config('trial_landings.'.$request->route('variant'));
    }
}
