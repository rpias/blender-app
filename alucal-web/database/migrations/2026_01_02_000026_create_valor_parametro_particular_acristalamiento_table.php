<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAlucal.s3db / tabla original "ValorParametroParticularAcristalamiento".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valor_parametro_particular_acristalamiento', function (Blueprint $table) {
            $table->id();
            $table->string('tipoAcristalamiento', 30);
            $table->string('materialAcristalamientoEntero', 10);
            $table->string('materialAcristalamientoSuperior', 10);
            $table->string('materialAcristalamientoInferior', 10);
            $table->string('cristalEntero', 30);
            $table->string('cristalSuperior', 30);
            $table->string('cristalInferior', 30);
            $table->string('divisor', 15)->nullable();
            $table->char('tablillaEnteraVertical', 1)->nullable();
            $table->char('tablillaSuperiorVertical', 1)->nullable();
            $table->char('tablillaInferiorVertical', 1)->nullable();
            $table->string('dimensionesColonialEntero', 5);
            $table->string('dimensionesColonialSuperior', 5);
            $table->string('dimensionesColonialInferior', 5);
            $table->string('tipoAcristalamiento2', 30);
            $table->string('materialAcristalamientoEntero2', 10);
            $table->string('materialAcristalamientoSuperior2', 10);
            $table->string('materialAcristalamientoInferior2', 10);
            $table->string('cristalEntero2', 30);
            $table->string('cristalSuperior2', 30);
            $table->string('cristalInferior2', 30);
            $table->string('divisor2', 15)->nullable();
            $table->char('tablillaEnteraVertical2', 1)->nullable();
            $table->char('tablillaSuperiorVertical2', 1)->nullable();
            $table->char('tablillaInferiorVertical2', 1)->nullable();
            $table->string('dimensionesColonialEntero2', 5);
            $table->string('dimensionesColonialSuperior2', 5);
            $table->string('dimensionesColonialInferior2', 5);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valor_parametro_particular_acristalamiento');
    }
};
