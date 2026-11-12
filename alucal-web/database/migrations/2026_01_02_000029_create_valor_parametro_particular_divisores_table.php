<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAlucal.s3db / tabla original "ValorParametroParticularDivisores".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valor_parametro_particular_divisores', function (Blueprint $table) {
            $table->id();
            $table->integer('cantDivisoresHorizontales');
            $table->string('divisorHorizontal', 16)->nullable();
            $table->integer('cantDivisoresVerticales');
            $table->string('divisorVertical', 16)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valor_parametro_particular_divisores');
    }
};
