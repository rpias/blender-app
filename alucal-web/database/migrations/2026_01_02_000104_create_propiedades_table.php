<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDConfiguracion.s3db / tabla original "Propiedades".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('propiedades', function (Blueprint $table) {
            $table->string('clave')->unique();
            $table->string('valor', 255);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('propiedades');
    }
};
