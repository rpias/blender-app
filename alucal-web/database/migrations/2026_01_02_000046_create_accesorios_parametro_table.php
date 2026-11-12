<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDDefinicionesAberturas.s3db / tabla original "accesoriosParametro".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accesorios_parametro', function (Blueprint $table) {
            $table->id();
            $table->integer('idDefinicionParametro');
            $table->string('accesorio', 20);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accesorios_parametro');
    }
};
