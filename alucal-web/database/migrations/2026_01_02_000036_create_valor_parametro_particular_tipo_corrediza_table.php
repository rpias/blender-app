<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAlucal.s3db / tabla original "ValorParametroParticularTipoCorrediza".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valor_parametro_particular_tipo_corrediza', function (Blueprint $table) {
            $table->id();
            $table->string('tipoCorrediza', 50);
            $table->string('mosquitero', 16)->nullable();
            $table->string('encuentroATope', 16)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valor_parametro_particular_tipo_corrediza');
    }
};
