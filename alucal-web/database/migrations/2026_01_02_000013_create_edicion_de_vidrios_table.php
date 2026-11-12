<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAlucal.s3db / tabla original "EdicionDeVidrios".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edicion_de_vidrios', function (Blueprint $table) {
            $table->id();
            $table->integer('idAberturaSimple');
            $table->string('tipoEdicion', 16);
            $table->dateTime('timestamp');
            $table->string('vidrio', 20);
            $table->string('descripcion', 40);
            $table->integer('ancho');
            $table->integer('alto');
            $table->integer('cantidad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edicion_de_vidrios');
    }
};
