<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAlucal.s3db / tabla original "ValorParametroParticularPremarco".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valor_parametro_particular_premarco', function (Blueprint $table) {
            $table->id();
            $table->boolean('llevaPremarco');
            $table->boolean('llevaTapajuntas');
            $table->string('tipoPremarco', 15)->nullable();
            $table->string('tipoTapajuntas', 15)->nullable();
            $table->string('codPremarco', 12)->nullable();
            $table->string('codTapajuntas', 12)->nullable();
            $table->integer('luzSupEnMM')->nullable();
            $table->integer('luzDerEnMM')->nullable();
            $table->integer('luzInfEnMM')->nullable();
            $table->integer('luzIzqEnMM')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valor_parametro_particular_premarco');
    }
};
