<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDConfiguracion.s3db / tabla original "CostoUniblockMixArmado".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('costo_uniblock_mix_armado', function (Blueprint $table) {
            $table->string('enumCajon', 20);
            $table->string('enumCortina', 20);
            $table->double('costoMinimo');
            $table->double('costoMetroCuadrado');
            $table->double('costoLargoCajon');
            $table->double('costo2doComando');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('costo_uniblock_mix_armado');
    }
};
