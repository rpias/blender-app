<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDConfiguracion.s3db / tabla original "Empresa".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresa', function (Blueprint $table) {
            $table->string('numero', 10)->nullable();
            $table->string('nombre', 60)->nullable();
            $table->string('alias', 60)->nullable();
            $table->string('direccion', 60)->nullable();
            $table->string('telefono', 60)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresa');
    }
};
