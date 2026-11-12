<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDDefinicionesAberturas.s3db / tabla original "definicionParametroTipoCorrediza".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('definicion_parametro_tipo_corrediza', function (Blueprint $table) {
            $table->id();
            $table->string('idDefinicionParametro')->unique();
            $table->integer('maxCantHojas')->nullable();
            $table->boolean('tieneMarco2Guias');
            $table->boolean('tieneMarco3Guias');
            $table->boolean('tieneMarco4Guias');
            $table->boolean('opcionMosquitero');
            $table->string('mosquiteros', 100)->nullable();
            $table->string('encuentrosATope', 50)->nullable();
            $table->boolean('mosqDebeUtilizarGuiaDeHoja')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('definicion_parametro_tipo_corrediza');
    }
};
