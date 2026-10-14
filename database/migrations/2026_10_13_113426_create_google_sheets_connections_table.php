<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_sheets_connections', function (Blueprint $table) {
            $table->id();
            $table->string('spreadsheet_id', 128)->nullable();
            $table->string('sheet_name', 100)->default('Contatti Local Up');
            $table->string('service_account_email')->nullable();
            $table->text('credentials_encrypted')->nullable();
            $table->boolean('enabled')->default(false);
            $table->text('last_error')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_sheets_connections');
    }
};
