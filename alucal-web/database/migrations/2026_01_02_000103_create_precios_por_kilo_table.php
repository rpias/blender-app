<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDConfiguracion.s3db / tabla original "PreciosPorKilo".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('precios_por_kilo', function (Blueprint $table) {
            $table->id();
            $table->string('terminacion', 35);
            $table->boolean('rpt')->nullable();
            $table->double('valor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('precios_por_kilo');
    }
};
