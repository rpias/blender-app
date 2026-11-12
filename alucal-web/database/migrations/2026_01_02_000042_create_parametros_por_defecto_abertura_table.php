<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDDefinicionesAberturas.s3db / tabla original "ParametrosPorDefectoAbertura".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parametros_por_defecto_abertura', function (Blueprint $table) {
            $table->id();
            $table->integer('idTipologia');
            $table->integer('idSerie');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parametros_por_defecto_abertura');
    }
};
