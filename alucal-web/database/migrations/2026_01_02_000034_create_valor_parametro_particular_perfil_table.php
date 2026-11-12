<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAlucal.s3db / tabla original "ValorParametroParticularPerfil".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valor_parametro_particular_perfil', function (Blueprint $table) {
            $table->id();
            $table->string('codigoPerfil', 14);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valor_parametro_particular_perfil');
    }
};
