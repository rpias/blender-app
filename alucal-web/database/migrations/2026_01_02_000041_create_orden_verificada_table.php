<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDDefinicionesAberturas.s3db / tabla original "OrdenVerificada".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orden_verificada', function (Blueprint $table) {
            $table->id();
            $table->integer('idTipologia');
            $table->integer('idSerie');
            $table->boolean('perfilesVerificados');
            $table->boolean('accesoriosVerificados');
            $table->boolean('vidriosVerificados');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orden_verificada');
    }
};
