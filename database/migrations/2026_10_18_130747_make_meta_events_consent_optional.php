<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('meta_events', function (Blueprint $table) {
            $table->uuid('consent_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('meta_events')->whereNull('consent_id')->exists()) {
            throw new RuntimeException('Impossibile rendere obbligatorio consent_id: esistono eventi Meta registrati senza consenso.');
        }

        Schema::table('meta_events', function (Blueprint $table) {
            $table->uuid('consent_id')->change();
        });
    }
};
