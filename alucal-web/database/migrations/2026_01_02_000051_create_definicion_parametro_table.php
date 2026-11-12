<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDDefinicionesAberturas.s3db / tabla original "definicionParametro".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('definicion_parametro', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 60);
            $table->string('tipoDeDato', 20);
            $table->string('categoria', 20)->nullable();
            $table->boolean('cambiaCantVidrios')->nullable();
            $table->string('descripcion', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('definicion_parametro');
    }
};
