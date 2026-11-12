<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDConfiguracion.s3db / tabla original "MargenesDesperdiciosCoefAjuste".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('margenes_desperdicios_coef_ajuste', function (Blueprint $table) {
            $table->integer('idTipologia')->nullable();
            $table->integer('idSerie')->nullable();
            $table->double('MargenMH')->nullable();
            $table->double('MargenMHFabrica')->nullable();
            $table->double('MargenMosq')->nullable();
            $table->double('MargenMosqFabrica')->nullable();
            $table->double('DespMH')->nullable();
            $table->double('DespMHFabrica')->nullable();
            $table->double('DespMosq')->nullable();
            $table->double('DespMosqFabrica')->nullable();
            $table->double('CoefAjusteObraParticular')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('margenes_desperdicios_coef_ajuste');
    }
};
