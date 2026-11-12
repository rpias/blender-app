<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAccesorios.s3db / tabla original "Uso".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uso', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uso');
    }
};
