<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDConfiguracion.s3db / tabla original "Anexo".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anexo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 80);
            $table->string('nombreArchivo')->unique();
            $table->string('palabrasClave', 255);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anexo');
    }
};
