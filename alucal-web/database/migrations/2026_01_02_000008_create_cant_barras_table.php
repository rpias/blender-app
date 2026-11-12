<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAlucal.s3db / tabla original "CantBarras".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cant_barras', function (Blueprint $table) {
            $table->id();
            $table->integer('idEstructura');
            $table->integer('idValorParametroParticular');
            $table->string('perfil', 14);
            $table->integer('cantidad');
            $table->integer('largoEnMM');
            $table->string('descripcion', 100);
            $table->string('parte', 20);
            $table->string('corte', 14);
            $table->string('terminacion', 40);
            $table->string('orientacion', 14);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cant_barras');
    }
};
