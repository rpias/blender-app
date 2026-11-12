<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAlucal.s3db / tabla original "EventoSeguimientoObra".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evento_seguimiento_obra', function (Blueprint $table) {
            $table->id();
            $table->integer('idObra');
            $table->date('fecha');
            $table->text('detalle');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_seguimiento_obra');
    }
};
