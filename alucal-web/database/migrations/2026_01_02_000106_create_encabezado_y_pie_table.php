<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDConfiguracion.s3db / tabla original "encabezado_y_pie".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('encabezado_y_pie', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('ruta_encabezado', 255);
            $table->string('ciudad', 100);
            $table->string('pie', 255);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encabezado_y_pie');
    }
};
