<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDConfiguracion.s3db / tabla original "Asesor".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asesor', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50);
            $table->string('celular', 30);
            $table->string('email', 80);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asesor');
    }
};
