<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAlucal.s3db / tabla original "PresupuestoParticularGenerado".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presupuesto_particular_generado', function (Blueprint $table) {
            $table->id();
            $table->string('tipologia', 30);
            $table->string('serie', 30);
            $table->string('medidas', 16);
            $table->dateTime('fechaHoraGenerado');
            $table->string('cliente', 50);
            $table->string('fileName', 30);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presupuesto_particular_generado');
    }
};
