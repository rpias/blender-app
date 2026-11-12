<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAlucal.s3db / tabla original "AccionNegocio".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accion_negocio', function (Blueprint $table) {
            $table->id();
            $table->integer('idNegocio');
            $table->date('fecha');
            $table->text('texto');
            $table->string('firma', 40);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accion_negocio');
    }
};
