<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAlucal.s3db / tabla original "EdicionDePerfiles".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edicion_de_perfiles', function (Blueprint $table) {
            $table->id();
            $table->integer('idAberturaSimple');
            $table->string('tipoEdicion', 16);
            $table->dateTime('timestamp');
            $table->integer('idPerfil');
            $table->integer('cantidad');
            $table->integer('largo');
            $table->string('descripcion', 80);
            $table->string('parte', 20);
            $table->string('corte', 16);
            $table->string('terminacion', 30);
            $table->string('orientacion', 16);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edicion_de_perfiles');
    }
};
