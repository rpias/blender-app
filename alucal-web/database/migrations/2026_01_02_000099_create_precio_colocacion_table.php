<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDConfiguracion.s3db / tabla original "PrecioColocacion".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('precio_colocacion', function (Blueprint $table) {
            $table->string('tipoColocacion')->unique();
            $table->integer('precioBase');
            $table->integer('precioPorMetroLineal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('precio_colocacion');
    }
};
