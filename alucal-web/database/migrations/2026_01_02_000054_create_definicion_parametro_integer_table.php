<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDDefinicionesAberturas.s3db / tabla original "definicionParametroInteger".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('definicion_parametro_integer', function (Blueprint $table) {
            $table->id();
            $table->integer('idDefinicionParametro');
            $table->string('cotaInferior', 20)->nullable();
            $table->string('cotaSuperior', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('definicion_parametro_integer');
    }
};
