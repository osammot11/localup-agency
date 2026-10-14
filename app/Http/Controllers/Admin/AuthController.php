<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function show(Request $request): Response|RedirectResponse
    {
        if ($request->user()?->is_admin) {
            return redirect()->route('admin.leads.index');
        }

        return response()->view('admin.login')
            ->header('Cache-Control', 'private, no-store, max-age=0');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials + ['is_admin' => true])) {
            return back()->withErrors(['email' => 'Credenziali non valide.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.leads.index'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
