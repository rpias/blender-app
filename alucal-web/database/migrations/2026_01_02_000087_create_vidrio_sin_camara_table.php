<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDVidrios.s3db / tabla original "VidrioSinCamara".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vidrio_sin_camara', function (Blueprint $table) {
            $table->id();
            $table->integer('idTipo');
            $table->double('precio');
            $table->double('precioFabrica')->default(0);
            $table->double('precioMinimo')->nullable();
            $table->double('precioMinimoFabrica')->nullable();
            $table->double('margen')->nullable();
            $table->double('margenFabrica')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vidrio_sin_camara');
    }
};
