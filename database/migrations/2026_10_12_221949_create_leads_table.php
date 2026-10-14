<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            $table->string('cognome', 100);
            $table->string('email');
            $table->string('telefono', 30);
            $table->string('attivita', 150);
            $table->string('settore', 150);
            $table->string('source', 100);
            $table->timestamp('consented_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
