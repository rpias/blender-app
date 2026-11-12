<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAlucal.s3db / tabla original "AberturaSimple".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abertura_simple', function (Blueprint $table) {
            $table->id();
            $table->integer('cantidad');
            $table->double('ancho');
            $table->double('alto');
            $table->integer('idTipologia');
            $table->integer('idSerie');
            $table->string('terminacion', 255)->nullable();
            $table->integer('numero')->nullable();
            $table->string('colorHerrajes', 20);
            $table->string('colorPlasticos', 20);
            $table->string('ubicacion', 60);
            $table->string('tipo', 10)->nullable();
            $table->integer('idElementoObra');
            $table->integer('idRellenoVano');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abertura_simple');
    }
};
