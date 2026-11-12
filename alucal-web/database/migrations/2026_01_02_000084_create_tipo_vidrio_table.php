<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDVidrios.s3db / tabla original "TipoVidrio".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_vidrio', function (Blueprint $table) {
            $table->id();
            $table->string('tipo')->unique();
            $table->string('abreviado', 5)->nullable();
            $table->boolean('puedeSerBorrado')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_vidrio');
    }
};
