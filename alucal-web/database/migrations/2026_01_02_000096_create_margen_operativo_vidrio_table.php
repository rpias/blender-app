<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDConfiguracion.s3db / tabla original "MargenOperativoVidrio".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('margen_operativo_vidrio', function (Blueprint $table) {
            $table->string('idVidrio')->unique();
            $table->double('margenOperativo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('margen_operativo_vidrio');
    }
};
