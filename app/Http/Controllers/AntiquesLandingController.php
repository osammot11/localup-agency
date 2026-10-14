<?php

namespace App\Http\Controllers;

use App\Services\LandingLeadService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AntiquesLandingController extends Controller
{
    public function show(): View
    {
        return view('social.antiques');
    }

    public function store(Request $request, LandingLeadService $leads): RedirectResponse
    {
        $data = $request->validate([
            'website' => ['nullable', 'max:0'],
            'nome' => ['required', 'string', 'max:100'],
            'cognome' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'telefono' => ['required', 'string', 'max:30'],
            'attivita' => ['required', 'string', 'max:150'],
            'settore' => ['required', 'string', 'max:150'],
            'consenso' => ['accepted'],
        ]);

        return $leads->submit($request, $data, 'social/antiquariato', 'antiques.landing');
    }
}
