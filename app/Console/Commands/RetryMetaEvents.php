<?php

namespace App\Console\Commands;

use App\Services\MetaConversionsApi;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('meta:retry')]
#[Description('Ritenta gli eventi Meta CAPI in attesa, entro 24 ore dalla conversione')]
class RetryMetaEvents extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(MetaConversionsApi $meta): int
    {
        $eventIds = DB::table('meta_events')
            ->where('status', 'pending')
            ->where('attempts', '<', 5)
            ->where('created_at', '>=', now()->subDay())
            ->orderBy('created_at')
            ->limit(100)
            ->pluck('id');

        foreach ($eventIds as $eventId) {
            $meta->deliver($eventId);
        }

        $this->info("Elaborati {$eventIds->count()} eventi Meta in attesa.");

        return self::SUCCESS;
    }
}
