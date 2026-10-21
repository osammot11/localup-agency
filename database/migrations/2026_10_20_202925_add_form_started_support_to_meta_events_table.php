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
            $table->foreignId('lead_id')->nullable()->change();
            $table->string('event_name', 32)->default('Lead');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('meta_events')->whereNull('lead_id')->exists()) {
            throw new RuntimeException('Impossibile rendere obbligatorio lead_id: esistono eventi senza contatto.');
        }

        Schema::table('meta_events', function (Blueprint $table) {
            $table->dropColumn('event_name');
            $table->foreignId('lead_id')->nullable(false)->change();
        });
    }
};
