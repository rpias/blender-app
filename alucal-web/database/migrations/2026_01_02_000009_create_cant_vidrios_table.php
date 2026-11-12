<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAlucal.s3db / tabla original "CantVidrios".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cant_vidrios', function (Blueprint $table) {
            $table->id();
            $table->integer('idEstructura');
            $table->integer('cantidad');
            $table->double('anchoEnMetros');
            $table->double('altoEnMetros');
            $table->integer('idVidrioSinCamara')->nullable();
            $table->integer('idVidrioExterior')->nullable();
            $table->integer('espesorCamaraEnMM')->nullable();
            $table->integer('idVidrioInterior')->nullable();
            $table->string('descripcion', 40)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cant_vidrios');
    }
};
