<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAlucal.s3db / tabla original "Obra".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obra', function (Blueprint $table) {
            $table->id();
            $table->integer('idCarpinteria')->nullable();
            $table->string('numero')->unique();
            $table->string('nombre', 255);
            $table->string('creador', 50)->nullable();
            $table->date('fechaCreacion')->nullable();
            $table->date('fechaUltModificacion')->nullable();
            $table->string('arquitecto', 50)->nullable();
            $table->string('cliente', 50)->nullable();
            $table->string('ubicacion', 50)->nullable();
            $table->string('estadoSeguimiento', 10)->nullable();
            $table->date('proximaAccionFecha')->nullable();
            $table->string('proximaAccionDetalle', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obra');
    }
};
