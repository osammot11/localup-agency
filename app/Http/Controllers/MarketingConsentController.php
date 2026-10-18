<?php

namespace App\Http\Controllers;

use App\Services\MarketingConsent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MarketingConsentController extends Controller
{
    public function store(Request $request, MarketingConsent $consent): RedirectResponse
    {
        $data = $request->validate([
            'choice' => ['required', 'in:granted,rejected'],
            'return_to' => ['required', Rule::in(['/', '/social/ristoranti', '/social/ristoranti-v2', '/social/beauty', '/social/beauty-v2', '/social/antiquariato', '/social/antiquariato-v2', '/social/pasticcerie', '/social/pasticcerie-v2', '/siti', '/siti-v2'])],
        ]);

        $cookie = $consent->record($request, $data['choice']);

        return redirect($data['return_to'])->withCookie($cookie);
    }
}
