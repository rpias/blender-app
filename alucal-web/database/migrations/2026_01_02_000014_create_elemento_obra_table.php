<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAlucal.s3db / tabla original "ElementoObra".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('elemento_obra', function (Blueprint $table) {
            $table->id();
            $table->string('tipoElemento', 20);
            $table->integer('idObra');
            $table->integer('numero');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('elemento_obra');
    }
};
