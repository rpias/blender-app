<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAlucal.s3db / tabla original "AberturaCompuesta_2".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abertura_compuesta_2', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 10);
            $table->integer('numero');
            $table->integer('cantidad');
            $table->string('ubicacion', 60);
            $table->integer('idHijo');
            $table->integer('idElementoObra');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abertura_compuesta_2');
    }
};
