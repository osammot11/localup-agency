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
            'return_to' => ['required', Rule::in(['/', '/social/ristoranti', '/social/beauty', '/social/antiquariato', '/social/pasticcerie', '/siti'])],
        ]);

        $cookie = $consent->record($request, $data['choice']);

        return redirect($data['return_to'])->withCookie($cookie);
    }
}
