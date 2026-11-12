<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAlucal.s3db / tabla original "EstructuraAluminio".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estructura_aluminio', function (Blueprint $table) {
            $table->id();
            $table->integer('numero');
            $table->integer('ancho');
            $table->integer('alto');
            $table->string('descripcion', 100);
            $table->integer('cantidad');
            $table->string('tipo', 10);
            $table->string('ubicacion', 60);
            $table->binary('dibujo')->nullable();
            $table->integer('idElementoObra');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estructura_aluminio');
    }
};
