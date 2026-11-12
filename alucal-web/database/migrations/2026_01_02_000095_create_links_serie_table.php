<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDConfiguracion.s3db / tabla original "LinksSerie".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('links_serie', function (Blueprint $table) {
            $table->integer('idSerie');
            $table->string('linkInformacionGeneral', 255);
            $table->string('linkCatalogoTecnico', 255);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('links_serie');
    }
};
