<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAlucal.s3db / tabla original "Negocio".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('negocio', function (Blueprint $table) {
            $table->id();
            $table->date('fechaCreado');
            $table->boolean('vivo');
            $table->integer('numero');
            $table->string('titulo', 255);
            $table->boolean('tieneCarpeta');
            $table->integer('montoUSD');
            $table->string('gestor', 50);
            $table->date('fechaProximaAccion');
            $table->string('estado', 20);
            $table->integer('resultadoUSD');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('negocio');
    }
};
