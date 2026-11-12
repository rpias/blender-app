<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDConfiguracion.s3db / tabla original "PrecioMetroLinealPremarco".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('precio_metro_lineal_premarco', function (Blueprint $table) {
            $table->id();
            $table->string('perfil')->unique();
            $table->double('precio');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('precio_metro_lineal_premarco');
    }
};
