<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDDefinicionesAberturas.s3db / tabla original "definicionParametroOpcionMasOtro".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('definicion_parametro_opcion_mas_otro', function (Blueprint $table) {
            $table->id();
            $table->string('idDefinicionParametro')->unique('uq_001');
            $table->integer('idDefParametroOptions');
            $table->integer('idDefParametroOtro');
            $table->string('optionsDeVisibilidad', 255);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('definicion_parametro_opcion_mas_otro');
    }
};
