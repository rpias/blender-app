<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAlucal.s3db / tabla original "AberturaCompuesta_2_Premarco".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abertura_compuesta_2_premarco', function (Blueprint $table) {
            $table->id();
            $table->string('numPerfil', 12);
            $table->integer('largo');
            $table->integer('cantidad');
            $table->string('corte', 12);
            $table->string('orientacion', 10);
            $table->integer('idAberturaCompuesta');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abertura_compuesta_2_premarco');
    }
};
