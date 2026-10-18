<?php

namespace App\Services;

use App\Models\Lead;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class GoogleSheetsSync
{
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    private const SHEETS_URL = 'https://sheets.googleapis.com/v4/spreadsheets/';

    private const HEADERS = ['ID contatto', 'Ricevuto (Europe/Rome)', 'Nome', 'Cognome', 'Email', 'Telefono', 'Attività', 'Settore', 'Sito web', 'Landing', 'Consenso al ricontatto (Europe/Rome)', 'Comune', 'Profilo Instagram'];

    /** @return array{exported: int, remaining: int} */
    public function sync(int $limit = 50): array
    {
        $settings = DB::table('google_sheets_connections')->where('id', 1)->first();

        if (! $settings || ! $settings->enabled || ! $settings->spreadsheet_id || ! $settings->credentials_encrypted) {
            throw new RuntimeException('Configura e attiva Google Sheets prima di sincronizzare.');
        }

        $lock = Cache::lock('google-sheets-sync', 600);

        if (! $lock->get()) {
            throw new RuntimeException('È già in corso una sincronizzazione. Riprova tra poco.');
        }

        try {
            $token = $this->accessToken($settings->credentials_encrypted);
            $this->ensureSheet($token, $settings->spreadsheet_id, $settings->sheet_name);
            $existingIds = $this->existingLeadIds($token, $settings->spreadsheet_id, $settings->sheet_name);
            $exported = 0;

            foreach ($this->pendingLeads($settings->spreadsheet_id, $settings->sheet_name, $limit) as $lead) {
                if (! isset($existingIds[(string) $lead->id])) {
                    $this->appendRow($token, $settings->spreadsheet_id, $settings->sheet_name, $this->leadRow($lead));
                    $existingIds[(string) $lead->id] = true;
                }

                DB::table('google_sheets_exports')->insertOrIgnore([
                    'lead_id' => $lead->id,
                    'spreadsheet_id' => $settings->spreadsheet_id,
                    'sheet_name' => $settings->sheet_name,
                    'exported_at' => now(),
                ]);
                $exported++;
            }

            $this->currentConnection($settings)->update([
                'last_error' => null,
                'last_synced_at' => now(),
                'updated_at' => now(),
            ]);

            return ['exported' => $exported, 'remaining' => $this->pendingCount($settings->spreadsheet_id, $settings->sheet_name)];
        } catch (Throwable $exception) {
            $this->currentConnection($settings)->update([
                'last_error' => $exception instanceof ConnectionException
                    ? 'Google non è raggiungibile. La sincronizzazione verrà ritentata.'
                    : ($exception instanceof RuntimeException ? $exception->getMessage() : 'Errore imprevisto durante la sincronizzazione.'),
                'updated_at' => now(),
            ]);

            throw $exception;
        } finally {
            $lock->release();
        }
    }

    public function pendingCount(string $spreadsheetId, string $sheetName): int
    {
        return Lead::query()->whereNotIn('id', DB::table('google_sheets_exports')
            ->select('lead_id')->where('spreadsheet_id', $spreadsheetId)->where('sheet_name', $sheetName))->count();
    }

    private function pendingLeads(string $spreadsheetId, string $sheetName, int $limit): iterable
    {
        return Lead::query()->whereNotIn('id', DB::table('google_sheets_exports')
            ->select('lead_id')->where('spreadsheet_id', $spreadsheetId)->where('sheet_name', $sheetName))
            ->orderBy('id')->limit($limit)->get();
    }

    private function currentConnection(object $settings): Builder
    {
        return DB::table('google_sheets_connections')
            ->where('id', 1)
            ->where('spreadsheet_id', $settings->spreadsheet_id)
            ->where('sheet_name', $settings->sheet_name)
            ->where('credentials_encrypted', $settings->credentials_encrypted);
    }

    private function accessToken(string $encryptedCredentials): string
    {
        $credentials = json_decode(Crypt::decryptString($encryptedCredentials), true, flags: JSON_THROW_ON_ERROR);
        $issuedAt = time();
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];

        if ($credentials['private_key_id'] !== '') {
            $header['kid'] = $credentials['private_key_id'];
        }

        $claims = [
            'iss' => $credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/spreadsheets',
            'aud' => self::TOKEN_URL,
            'iat' => $issuedAt,
            'exp' => $issuedAt + 3600,
        ];
        $unsigned = $this->base64Url(json_encode($header, JSON_THROW_ON_ERROR)).'.'.$this->base64Url(json_encode($claims, JSON_THROW_ON_ERROR));

        if (! openssl_sign($unsigned, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('La chiave privata Google non è valida.');
        }

        $response = Http::asForm()->connectTimeout(3)->timeout(10)->post(self::TOKEN_URL, [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $unsigned.'.'.$this->base64Url($signature),
        ]);

        if (! $response->successful() || ! is_string($response->json('access_token'))) {
            throw new RuntimeException('Autenticazione Google non riuscita (HTTP '.$response->status().'). Controlla le credenziali del service account.');
        }

        return $response->json('access_token');
    }

    private function ensureSheet(string $token, string $spreadsheetId, string $sheetName): void
    {
        $base = self::SHEETS_URL.$spreadsheetId;
        $response = $this->check(Http::withToken($token)->connectTimeout(3)->timeout(10)
            ->get($base, ['fields' => 'sheets.properties.title']));
        $titles = array_column(array_column($response->json('sheets', []), 'properties'), 'title');

        if (! in_array($sheetName, $titles, true)) {
            $this->check(Http::withToken($token)->connectTimeout(3)->timeout(10)
                ->post($base.':batchUpdate', ['requests' => [['addSheet' => ['properties' => ['title' => $sheetName]]]]]));
        }

        $headerRange = $this->range($sheetName, 'A1:M1');
        $headerResponse = $this->check(Http::withToken($token)->connectTimeout(3)->timeout(10)
            ->get($base.'/values/'.$headerRange));
        $existingHeader = $headerResponse->json('values.0');

        if ($existingHeader === null) {
            $this->appendRow($token, $spreadsheetId, $sheetName, self::HEADERS);
        } elseif ($existingHeader === array_slice(self::HEADERS, 0, 11)) {
            $this->check(Http::withToken($token)->connectTimeout(3)->timeout(10)
                ->put($base.'/values/'.$this->range($sheetName, 'L1:M1').'?valueInputOption=RAW', [
                    'majorDimension' => 'ROWS',
                    'values' => [array_slice(self::HEADERS, 11)],
                ]));
        } elseif ($existingHeader !== self::HEADERS) {
            throw new RuntimeException('La scheda selezionata contiene intestazioni diverse. Scegli una scheda vuota o una già creata da Local Up.');
        }
    }

    private function existingLeadIds(string $token, string $spreadsheetId, string $sheetName): array
    {
        $response = $this->check(Http::withToken($token)->connectTimeout(3)->timeout(10)
            ->get(self::SHEETS_URL.$spreadsheetId.'/values/'.$this->range($sheetName, 'A2:A')));
        $ids = [];

        foreach ($response->json('values', []) as $row) {
            if (isset($row[0])) {
                $ids[(string) $row[0]] = true;
            }
        }

        return $ids;
    }

    private function appendRow(string $token, string $spreadsheetId, string $sheetName, array $row): void
    {
        $url = self::SHEETS_URL.$spreadsheetId.'/values/'.$this->range($sheetName, 'A:M')
            .':append?valueInputOption=RAW&insertDataOption=INSERT_ROWS';
        $response = $this->check(Http::withToken($token)->connectTimeout(3)->timeout(12)
            ->post($url, ['majorDimension' => 'ROWS', 'values' => [$row]]));

        if ($response->json('updates.updatedRows') !== 1) {
            throw new RuntimeException('Google Sheets non ha confermato la riga. Il prossimo tentativo controllerà se è già presente.');
        }
    }

    private function leadRow(Lead $lead): array
    {
        return [
            (string) $lead->id,
            $lead->created_at?->timezone('Europe/Rome')->format('Y-m-d H:i:s') ?? '',
            $lead->nome,
            $lead->cognome,
            $lead->email,
            $lead->telefono,
            $lead->attivita,
            $lead->settore,
            $lead->sito_attuale ?? '',
            $lead->source,
            $lead->consented_at?->timezone('Europe/Rome')->format('Y-m-d H:i:s') ?? '',
            $lead->comune ?? '',
            $lead->profilo_instagram ?? '',
        ];
    }

    private function check(Response $response): Response
    {
        if (! $response->successful()) {
            $hint = match ($response->status()) {
                403 => ' Condividi il foglio con il service account come Editor e verifica che Google Sheets API sia attiva.',
                404 => ' Controlla il link del foglio e i permessi del service account.',
                default => '',
            };

            throw new RuntimeException('Google Sheets ha risposto HTTP '.$response->status().'.'.$hint);
        }

        return $response;
    }

    private function range(string $sheetName, string $cells): string
    {
        return rawurlencode("'{$sheetName}'!{$cells}");
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
