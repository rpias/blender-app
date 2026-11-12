<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDConfiguracion.s3db / tabla original "UltimaSerieCalculada".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ultima_serie_calculada', function (Blueprint $table) {
            $table->string('Tipologia')->unique();
            $table->string('Serie', 30)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ultima_serie_calculada');
    }
};
