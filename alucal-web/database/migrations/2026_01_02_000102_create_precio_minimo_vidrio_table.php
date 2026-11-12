<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDConfiguracion.s3db / tabla original "PrecioMinimoVidrio".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('precio_minimo_vidrio', function (Blueprint $table) {
            $table->string('idVidrio')->unique();
            $table->double('precioMinimo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('precio_minimo_vidrio');
    }
};
