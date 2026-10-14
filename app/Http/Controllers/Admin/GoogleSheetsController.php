<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Services\GoogleSheetsSync;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class GoogleSheetsController extends Controller
{
    public function show(GoogleSheetsSync $sync): Response
    {
        $settings = DB::table('google_sheets_connections')->where('id', 1)->first();
        $total = Lead::query()->count();
        $pending = $settings?->spreadsheet_id ? $sync->pendingCount($settings->spreadsheet_id, $settings->sheet_name) : $total;

        return response()->view('admin.google-sheets', compact('settings', 'total', 'pending'))
            ->header('Cache-Control', 'private, no-store, max-age=0');
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'spreadsheet' => ['nullable', 'string', 'max:500'],
            'sheet_name' => ['required', 'string', 'max:100', 'regex:/^[\pL\pN _-]+$/u'],
            'credentials_json' => ['nullable', 'string', 'max:20000'],
            'enabled' => ['nullable', 'boolean'],
        ]);
        $existing = DB::table('google_sheets_connections')->where('id', 1)->first();
        $spreadsheetId = $this->spreadsheetId(trim($data['spreadsheet'] ?? ''));
        $credentials = $existing?->credentials_encrypted;
        $email = $existing?->service_account_email;

        if (! empty($data['credentials_json'])) {
            [$credentials, $email] = $this->credentials($data['credentials_json']);
        }

        $enabled = (bool) ($data['enabled'] ?? false);

        if ($enabled && (! $spreadsheetId || ! $credentials)) {
            throw ValidationException::withMessages([
                'spreadsheet' => 'Per attivare la sincronizzazione servono un foglio Google e le credenziali del service account.',
            ]);
        }

        DB::table('google_sheets_connections')->updateOrInsert(['id' => 1], [
            'spreadsheet_id' => $spreadsheetId,
            'sheet_name' => trim($data['sheet_name']),
            'credentials_encrypted' => $credentials,
            'service_account_email' => $email,
            'enabled' => $enabled,
            'last_error' => null,
            'last_synced_at' => $existing?->spreadsheet_id === $spreadsheetId
                && $existing?->sheet_name === trim($data['sheet_name'])
                && $existing?->credentials_encrypted === $credentials
                ? $existing?->last_synced_at : null,
            'created_at' => $existing?->created_at ?? now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.google-sheets.show')->with('status', 'Impostazioni salvate. Usa “Sincronizza ora” per verificare il collegamento.');
    }

    public function sync(GoogleSheetsSync $sync): RedirectResponse
    {
        try {
            $result = $sync->sync(10);

            return redirect()->route('admin.google-sheets.show')->with('status',
                "Sincronizzazione riuscita: {$result['exported']} contatti elaborati, {$result['remaining']} ancora da inviare.");
        } catch (RuntimeException $exception) {
            return redirect()->route('admin.google-sheets.show')->with('error', $exception->getMessage());
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('admin.google-sheets.show')->with('error', 'Sincronizzazione non riuscita. Riprova tra poco.');
        }
    }

    private function spreadsheetId(string $input): ?string
    {
        if ($input === '') {
            return null;
        }

        if (preg_match('/^[A-Za-z0-9_-]{20,128}$/', $input)) {
            return $input;
        }

        $parts = parse_url($input);

        if (! is_array($parts) || ($parts['scheme'] ?? '') !== 'https' || ($parts['host'] ?? '') !== 'docs.google.com'
            || ! preg_match('~^/spreadsheets/d/([A-Za-z0-9_-]{20,128})(?:/|$)~', $parts['path'] ?? '', $matches)) {
            throw ValidationException::withMessages(['spreadsheet' => 'Inserisci un link valido di Google Sheets o il suo ID.']);
        }

        return $matches[1];
    }

    /** @return array{string, string} */
    private function credentials(string $input): array
    {
        try {
            $value = json_decode($input, true, flags: JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            throw ValidationException::withMessages(['credentials_json' => 'Il file JSON delle credenziali non è valido.']);
        }

        $email = $value['client_email'] ?? null;
        $privateKey = $value['private_key'] ?? null;
        $keyId = $value['private_key_id'] ?? '';
        $key = is_string($privateKey) ? openssl_pkey_get_private($privateKey) : false;
        $details = $key ? openssl_pkey_get_details($key) : false;

        if (($value['type'] ?? null) !== 'service_account' || ! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)
            || ! str_ends_with($email, '.gserviceaccount.com') || ! is_string($keyId)
            || ! $details || $details['type'] !== OPENSSL_KEYTYPE_RSA) {
            throw ValidationException::withMessages(['credentials_json' => 'Servono credenziali JSON valide di un service account Google.']);
        }

        $safeCredentials = json_encode([
            'client_email' => $email,
            'private_key' => $privateKey,
            'private_key_id' => $keyId,
        ], JSON_THROW_ON_ERROR);

        return [Crypt::encryptString($safeCredentials), $email];
    }
}
