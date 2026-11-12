<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAlucal.s3db / tabla original "ValorParametroParticularUniblockMix".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valor_parametro_particular_uniblock_mix', function (Blueprint $table) {
            $table->id();
            $table->boolean('conCortina');
            $table->string('materialCortina', 8)->nullable();
            $table->string('materialCajon', 8)->nullable();
            $table->integer('altoCajon')->nullable();
            $table->boolean('sonMedidasVano')->nullable();
            $table->string('jamba', 25)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valor_parametro_particular_uniblock_mix');
    }
};
