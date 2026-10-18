<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class GoogleSheetsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private const SHEET_ID = 'testSpreadsheetId123456789012345';

    private function lead(string $email = 'mario@example.com'): Lead
    {
        return Lead::create([
            'nome' => 'Mario', 'cognome' => 'Rossi', 'email' => $email,
            'telefono' => '+39 320 000 0000', 'attivita' => 'Osteria',
            'settore' => 'Ristorazione', 'sito_attuale' => 'https://osteria.example.com',
            'source' => 'social/ristoranti', 'consented_at' => now(),
        ]);
    }

    private function credentials(): string
    {
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($key, $privateKey);

        return json_encode([
            'type' => 'service_account',
            'client_email' => 'local-up@test-project.iam.gserviceaccount.com',
            'private_key' => $privateKey,
            'private_key_id' => 'test-key-id',
            'token_uri' => 'https://untrusted.example.com/token',
        ], JSON_THROW_ON_ERROR);
    }

    private function configure(?string $credentials = null, string $sheetId = self::SHEET_ID): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->post(route('admin.google-sheets.update'), [
            'spreadsheet' => 'https://docs.google.com/spreadsheets/d/'.$sheetId.'/edit',
            'sheet_name' => 'Contatti Local Up',
            'credentials_json' => $credentials ?? $this->credentials(),
            'enabled' => '1',
        ])->assertRedirect(route('admin.google-sheets.show'));
    }

    private function fakeGoogle(array &$rows, ?int $appendStatus = null): void
    {
        Http::swap(new Factory);
        Http::preventStrayRequests();
        Http::fake(function (ClientRequest $request) use (&$rows, $appendStatus) {
            $url = $request->url();

            if ($url === 'https://oauth2.googleapis.com/token') {
                return Http::response(['access_token' => 'test-access-token']);
            }
            if (str_contains($url, ':batchUpdate')) {
                return Http::response(['replies' => [['addSheet' => []]]]);
            }
            if ($request->method() === 'PUT' && str_contains($url, 'L1%3AM1')) {
                $rows[0] = array_merge($rows[0], $request['values'][0]);

                return Http::response(['updatedCells' => 2]);
            }
            if (str_contains($url, '/values/') && str_contains($url, ':append')) {
                if ($appendStatus !== null) {
                    return Http::response(['error' => 'temporary'], $appendStatus);
                }
                $rows[] = $request['values'][0];

                return Http::response(['updates' => ['updatedRows' => 1]]);
            }
            if (str_contains($url, '/values/') && str_contains($url, 'A1%3AM1')) {
                return Http::response(['values' => $rows ? [$rows[0]] : []]);
            }
            if (str_contains($url, '/values/') && str_contains($url, 'A2%3AA')) {
                return Http::response(['values' => array_map(fn (array $row) => [$row[0]], array_slice($rows, 1))]);
            }
            if (str_starts_with($url, 'https://sheets.googleapis.com/v4/spreadsheets/')) {
                return Http::response(['sheets' => [['properties' => ['title' => 'Contatti Local Up']]]]);
            }

            throw new RuntimeException('Richiesta inattesa: '.$url);
        });
    }

    public function test_only_admin_can_manage_google_sheets(): void
    {
        foreach (['admin.google-sheets.show', 'admin.google-sheets.update', 'admin.google-sheets.sync'] as $route) {
            $response = $route === 'admin.google-sheets.show' ? $this->get(route($route)) : $this->post(route($route));
            $response->assertRedirect(route('login'));
        }

        $this->actingAs(User::factory()->create());
        $this->get(route('admin.google-sheets.show'))->assertForbidden();
        $this->post(route('admin.google-sheets.update'))->assertForbidden();
        $this->post(route('admin.google-sheets.sync'))->assertForbidden();
    }

    public function test_admin_can_save_and_rotate_credentials_without_exposing_private_key(): void
    {
        $credentials = $this->credentials();
        $this->configure($credentials);
        $settings = DB::table('google_sheets_connections')->first();
        $this->assertSame(self::SHEET_ID, $settings->spreadsheet_id);
        $this->assertSame('local-up@test-project.iam.gserviceaccount.com', $settings->service_account_email);
        $this->assertStringNotContainsString('BEGIN PRIVATE KEY', $settings->credentials_encrypted);
        $this->assertStringNotContainsString('untrusted.example.com', Crypt::decryptString($settings->credentials_encrypted));
        $this->get(route('admin.google-sheets.show'))
            ->assertOk()->assertSee('local-up@test-project.iam.gserviceaccount.com')
            ->assertDontSee('BEGIN PRIVATE KEY')
            ->assertHeader('Cache-Control', 'max-age=0, no-store, private');

        $this->post(route('admin.google-sheets.update'), [
            'spreadsheet' => self::SHEET_ID,
            'sheet_name' => 'Nuova scheda',
            'credentials_json' => '',
            'enabled' => '0',
        ])->assertRedirect();
        $this->assertDatabaseHas('google_sheets_connections', ['id' => 1, 'enabled' => 0, 'sheet_name' => 'Nuova scheda']);
        $this->assertSame($settings->credentials_encrypted, DB::table('google_sheets_connections')->value('credentials_encrypted'));
    }

    public function test_invalid_destination_and_credentials_are_rejected(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->post(route('admin.google-sheets.update'), [
            'spreadsheet' => 'https://evil.example.com/spreadsheets/d/'.self::SHEET_ID.'/edit',
            'sheet_name' => 'Contatti Local Up', 'enabled' => '1',
        ])->assertSessionHasErrors('spreadsheet');
        $this->post(route('admin.google-sheets.update'), [
            'spreadsheet' => self::SHEET_ID, 'sheet_name' => 'Contatti Local Up',
            'credentials_json' => '{invalid', 'enabled' => '1',
        ])->assertSessionHasErrors('credentials_json')
            ->assertSessionMissing('_old_input.credentials_json');
        $this->assertDatabaseCount('google_sheets_connections', 0);
    }

    public function test_sync_exports_existing_leads_once_and_reconciles_remote_rows(): void
    {
        $lead = $this->lead();
        $this->configure();
        $rows = [];
        $this->fakeGoogle($rows);

        $this->post(route('admin.google-sheets.sync'))->assertSessionHas('status');
        $this->assertCount(2, $rows);
        $this->assertSame((string) $lead->id, $rows[1][0]);
        $this->assertSame('mario@example.com', $rows[1][4]);
        $this->assertDatabaseHas('google_sheets_exports', ['lead_id' => $lead->id, 'spreadsheet_id' => self::SHEET_ID, 'sheet_name' => 'Contatti Local Up']);
        $this->post(route('admin.google-sheets.sync'))->assertSessionHas('status');
        $this->assertCount(2, $rows);
        Http::assertSent(fn (ClientRequest $request) => $request->url() === 'https://oauth2.googleapis.com/token');
        Http::assertSent(fn (ClientRequest $request) => str_contains($request->url(), 'valueInputOption=RAW')
            && ($request['values'][0][0] ?? null) === (string) $lead->id);

        DB::table('google_sheets_exports')->delete();
        $this->post(route('admin.google-sheets.sync'))->assertSessionHas('status');
        $this->assertCount(2, $rows);
        $this->assertDatabaseCount('google_sheets_exports', 1);
    }

    public function test_existing_sheet_gains_restaurant_trial_columns_without_losing_previous_rows(): void
    {
        $lead = $this->lead();
        $lead->update(['comune' => 'Lucca', 'profilo_instagram' => '@osteria']);
        $this->configure();
        $rows = [
            ['ID contatto', 'Ricevuto (Europe/Rome)', 'Nome', 'Cognome', 'Email', 'Telefono', 'Attività', 'Settore', 'Sito web', 'Landing', 'Consenso al ricontatto (Europe/Rome)'],
            ['99', '2026-10-01 10:00:00', 'Altro', 'Cliente', 'altro@example.com', '123', 'Altro locale', 'Ristorazione', '', 'social/ristoranti', '2026-10-01 10:00:00'],
        ];
        $this->fakeGoogle($rows);

        $this->post(route('admin.google-sheets.sync'))->assertSessionHas('status');

        $this->assertSame('Comune', $rows[0][11]);
        $this->assertSame('Profilo Instagram', $rows[0][12]);
        $this->assertSame('99', $rows[1][0]);
        $this->assertSame('Lucca', $rows[2][11]);
        $this->assertSame('@osteria', $rows[2][12]);
        Http::assertSent(fn (ClientRequest $request) => $request->method() === 'PUT' && str_contains($request->url(), 'L1%3AM1'));
    }

    public function test_change_of_destination_backfills_existing_contacts(): void
    {
        $lead = $this->lead();
        $this->configure();
        $rows = [];
        $this->fakeGoogle($rows);
        $this->post(route('admin.google-sheets.sync'))->assertSessionHas('status');

        $newId = 'anotherSpreadsheetId12345678901234';
        $this->post(route('admin.google-sheets.update'), [
            'spreadsheet' => $newId, 'sheet_name' => 'Contatti Local Up',
            'credentials_json' => '', 'enabled' => '1',
        ])->assertRedirect();
        $this->assertDatabaseHas('google_sheets_exports', ['lead_id' => $lead->id, 'spreadsheet_id' => self::SHEET_ID, 'sheet_name' => 'Contatti Local Up']);
        $this->get(route('admin.google-sheets.show'))->assertSee('1</strong><span>da inviare a questo foglio', false);
        $newRows = [];
        $this->fakeGoogle($newRows);
        $this->post(route('admin.google-sheets.sync'))->assertSessionHas('status');
        $this->assertDatabaseHas('google_sheets_exports', ['lead_id' => $lead->id, 'spreadsheet_id' => $newId, 'sheet_name' => 'Contatti Local Up']);
        $this->assertCount(2, $newRows);
    }

    public function test_change_of_tab_in_same_spreadsheet_backfills_existing_contacts(): void
    {
        $lead = $this->lead();
        $this->configure();
        $rows = [];
        $this->fakeGoogle($rows);
        $this->post(route('admin.google-sheets.sync'))->assertSessionHas('status');

        $this->post(route('admin.google-sheets.update'), [
            'spreadsheet' => self::SHEET_ID,
            'sheet_name' => 'Nuova scheda',
            'credentials_json' => '',
            'enabled' => '1',
        ])->assertRedirect();
        $this->get(route('admin.google-sheets.show'))->assertSee('1</strong><span>da inviare a questo foglio', false);

        $newRows = [];
        $this->fakeGoogle($newRows);
        $this->post(route('admin.google-sheets.sync'))->assertSessionHas('status');
        $this->assertDatabaseHas('google_sheets_exports', ['lead_id' => $lead->id, 'spreadsheet_id' => self::SHEET_ID, 'sheet_name' => 'Nuova scheda']);
        $this->assertDatabaseCount('google_sheets_exports', 2);
        $this->assertCount(2, $newRows);
    }

    public function test_google_failure_keeps_leads_pending_and_shows_safe_error(): void
    {
        $this->lead();
        $this->configure();
        $rows = [];
        $this->fakeGoogle($rows, 403);

        $this->post(route('admin.google-sheets.sync'))->assertSessionHas('error');
        $this->assertDatabaseCount('leads', 1);
        $this->assertDatabaseCount('google_sheets_exports', 0);
        $this->assertDatabaseHas('google_sheets_connections', ['last_error' => 'Google Sheets ha risposto HTTP 403. Condividi il foglio con il service account come Editor e verifica che Google Sheets API sia attiva.']);
    }

    public function test_scheduled_command_exports_contacts_when_enabled(): void
    {
        $lead = $this->lead();
        $this->configure();
        $rows = [];
        $this->fakeGoogle($rows);

        $this->artisan('sheets:sync')->assertSuccessful();

        $this->assertDatabaseHas('google_sheets_exports', [
            'lead_id' => $lead->id,
            'spreadsheet_id' => self::SHEET_ID,
            'sheet_name' => 'Contatti Local Up',
        ]);
    }
}
