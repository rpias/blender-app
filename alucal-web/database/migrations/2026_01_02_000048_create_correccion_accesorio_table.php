<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDDefinicionesAberturas.s3db / tabla original "correccionAccesorio".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correccion_accesorio', function (Blueprint $table) {
            $table->integer('idCorreccionDescuento');
            $table->string('codigoAccesorio', 16);
            $table->string('tipoCorreccion', 8);
            $table->string('parte', 20);
            $table->double('cantidad')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correccion_accesorio');
    }
};
