<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDDefinicionesAberturas.s3db / tabla original "definicionParametroAcristalamiento".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('definicion_parametro_acristalamiento', function (Blueprint $table) {
            $table->id();
            $table->integer('idDefinicionParametro')->nullable();
            $table->boolean('acristalamientoCorr2Mas1');
            $table->boolean('opcionVidrioSinCamara');
            $table->boolean('opcionDVH');
            $table->boolean('opcionTablilla');
            $table->boolean('opcionDivisorAlMedio');
            $table->boolean('opcionColonial')->nullable();
            $table->string('divisores', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('definicion_parametro_acristalamiento');
    }
};
