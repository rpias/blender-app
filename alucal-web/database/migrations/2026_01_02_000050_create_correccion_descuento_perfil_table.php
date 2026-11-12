<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDDefinicionesAberturas.s3db / tabla original "correccionDescuentoPerfil".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correccion_descuento_perfil', function (Blueprint $table) {
            $table->integer('idCorreccionDescuento');
            $table->string('perfil', 20);
            $table->string('orientacion', 10);
            $table->integer('correccion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correccion_descuento_perfil');
    }
};
