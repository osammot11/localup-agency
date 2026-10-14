<?php

namespace App\Console\Commands;

use App\Services\GoogleSheetsSync;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

#[Signature('sheets:sync')]
#[Description('Sincronizza i contatti locali con il Google Sheet configurato nell’admin')]
class SyncGoogleSheets extends Command
{
    public function handle(GoogleSheetsSync $sync): int
    {
        if (! DB::table('google_sheets_connections')->where('id', 1)->where('enabled', true)->exists()) {
            return self::SUCCESS;
        }

        try {
            $result = $sync->sync();
            $this->info("Sincronizzati {$result['exported']} contatti; {$result['remaining']} in attesa.");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error('Sincronizzazione Google Sheets non riuscita. Controlla lo stato nel pannello admin.');
            report($exception);

            return self::FAILURE;
        }
    }
}
