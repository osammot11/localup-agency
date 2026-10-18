<?php

namespace App\Http\Controllers;

use App\Services\LandingLeadService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RestaurantTrialLandingController extends Controller
{
    public function show(): View
    {
        return view('social.restaurants-trial');
    }

    public function store(Request $request, LandingLeadService $leads): RedirectResponse
    {
        $data = $request->validate([
            'website' => ['nullable', 'max:0'],
            'attivita' => ['required', 'string', 'max:150'],
            'comune' => ['required', 'string', 'max:100'],
            'profilo_instagram' => ['nullable', 'string', 'max:255'],
            'nome' => ['required', 'string', 'max:100'],
            'telefono' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255'],
            'consenso' => ['accepted'],
        ]);

        return $leads->submit($request, $data + ['cognome' => '', 'settore' => 'Ristorazione'], 'social/ristoranti-v2', 'restaurants-trial.landing');
    }
}
