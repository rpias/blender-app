<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAccesorios.s3db / tabla original "PerfilDatosCargados".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perfil_datos_cargados', function (Blueprint $table) {
            $table->string('idPerfil')->unique();
            $table->boolean('usos');
            $table->boolean('series');
            $table->boolean('tubular');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perfil_datos_cargados');
    }
};
