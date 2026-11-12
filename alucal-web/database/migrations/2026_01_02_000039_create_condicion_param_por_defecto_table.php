<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDDefinicionesAberturas.s3db / tabla original "CondicionParamPorDefecto".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('condicion_param_por_defecto', function (Blueprint $table) {
            $table->id();
            $table->integer('idParamPorDefecto');
            $table->string('datoAsociado', 20);
            $table->integer('rangoDesde');
            $table->integer('rangoHasta');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('condicion_param_por_defecto');
    }
};
