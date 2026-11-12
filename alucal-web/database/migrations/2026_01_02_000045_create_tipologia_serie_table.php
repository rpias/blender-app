<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDDefinicionesAberturas.s3db / tabla original "TipologiaSerie".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipologia_serie', function (Blueprint $table) {
            $table->integer('idTipologia');
            $table->integer('idSerie');
            $table->boolean('usada');
            $table->integer('maxEspesorVidrioEnMM');
            $table->boolean('perfilesVerificados');
            $table->boolean('accesoriosVerificados');
            $table->boolean('vidriosVerificados');
            $table->double('cantHorasMinimo');
            $table->double('cantHorasChica');
            $table->double('cantHorasGrande');
            $table->integer('anchoChicaEnMM');
            $table->integer('altoChicaEnMM');
            $table->integer('anchoGrandeEnMM');
            $table->integer('altoGrandeEnMM');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipologia_serie');
    }
};
