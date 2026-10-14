<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_sheets_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->string('spreadsheet_id', 128);
            $table->timestamp('exported_at');
            $table->unique(['lead_id', 'spreadsheet_id']);
            $table->index(['spreadsheet_id', 'lead_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_sheets_exports');
    }
};
