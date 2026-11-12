<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDDefinicionesAberturas.s3db / tabla original "Tipologia".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipologia', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('abreviacion', 10)->nullable();
            $table->boolean('esMampara')->nullable();
            $table->boolean('puedeLlevarAcristalamiento')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipologia');
    }
};
