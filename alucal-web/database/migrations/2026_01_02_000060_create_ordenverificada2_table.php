<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDDefinicionesAberturas.s3db / tabla original "ordenverificada2".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ordenverificada2', function (Blueprint $table) {
            $table->id();
            $table->text('vectorDeVerificacion');
            $table->text('registradoPor');
            $table->integer('idSerie');
            $table->integer('idTipologia');
            $table->date('fechaVerificacion');
            $table->string('comentario', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ordenverificada2');
    }
};
