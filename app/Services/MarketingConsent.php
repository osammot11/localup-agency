<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;

class MarketingConsent
{
    public const COOKIE = 'localup_marketing_consent';

    public function state(Request $request): ?string
    {
        $id = $this->cookieId($request);

        if ($id === null) {
            return null;
        }

        return DB::table('marketing_consents')->where('id', $id)->value('status');
    }

    public function grantedId(Request $request): ?string
    {
        return $this->state($request) === 'granted' ? $this->cookieId($request) : null;
    }

    public function record(Request $request, string $choice): Cookie
    {
        $id = $this->cookieId($request) ?? (string) Str::uuid();
        $existing = DB::table('marketing_consents')->where('id', $id)->exists();
        $now = now();

        if ($existing) {
            DB::table('marketing_consents')->where('id', $id)->update([
                'status' => $choice,
                'granted_at' => $choice === 'granted' ? $now : null,
                'revoked_at' => $choice === 'rejected' ? $now : null,
                'updated_at' => $now,
            ]);
        } else {
            DB::table('marketing_consents')->insert([
                'id' => $id,
                'status' => $choice,
                'granted_at' => $choice === 'granted' ? $now : null,
                'revoked_at' => $choice === 'rejected' ? $now : null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        if ($choice === 'rejected') {
            DB::table('meta_events')->where('consent_id', $id)->where('status', 'pending')
                ->update(['status' => 'cancelled', 'updated_at' => $now]);
        }

        return cookie(self::COOKIE, $id, 60 * 24 * 180, secure: $request->isSecure(), sameSite: 'lax');
    }

    private function cookieId(Request $request): ?string
    {
        $id = $request->cookie(self::COOKIE);

        return is_string($id) && Str::isUuid($id) ? $id : null;
    }
}
