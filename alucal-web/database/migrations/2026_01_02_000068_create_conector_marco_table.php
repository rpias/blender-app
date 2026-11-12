<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAccesorios.s3db / tabla original "ConectorMarco".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conector_marco', function (Blueprint $table) {
            $table->string('codigoConector', 16);
            $table->string('codigoMarco', 16);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conector_marco');
    }
};
