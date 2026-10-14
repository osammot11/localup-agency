<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('google_sheets_exports', function (Blueprint $table) {
            $table->string('sheet_name', 100)->default('Contatti Local Up');
            $table->dropUnique(['lead_id', 'spreadsheet_id']);
            $table->unique(['lead_id', 'spreadsheet_id', 'sheet_name']);
        });
    }

    public function down(): void
    {
        Schema::table('google_sheets_exports', function (Blueprint $table) {
            $table->dropUnique(['lead_id', 'spreadsheet_id', 'sheet_name']);
            $table->unique(['lead_id', 'spreadsheet_id']);
            $table->dropColumn('sheet_name');
        });
    }
};
