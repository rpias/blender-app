<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAlucal.s3db / tabla original "ValorParametroParticularInteger".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valor_parametro_particular_integer', function (Blueprint $table) {
            $table->id();
            $table->integer('valor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valor_parametro_particular_integer');
    }
};
