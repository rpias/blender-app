<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAccesorios.s3db / tabla original "Perfil".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perfil', function (Blueprint $table) {
            $table->id();
            $table->string('Numero')->unique();
            $table->double('Peso');
            $table->string('Descripcion', 255)->nullable();
            $table->integer('esTubular')->nullable();
            $table->string('codigoAlternativo', 30)->nullable();
            $table->integer('minLargoUtilizable')->nullable();
            $table->string('comentario', 255)->nullable();
            $table->integer('descuentoConector')->nullable();
            $table->integer('rpt')->nullable();
            $table->string('premarcoPorDefecto', 14)->nullable();
            $table->string('tapajuntasPorDefecto', 14)->nullable();
            $table->integer('marcoConTapajuntasIncluido')->nullable();
            $table->integer('tieneCurva')->nullable();
            $table->integer('tieneCavaParaTornillo')->nullable();
            $table->integer('esSimetrico')->nullable();
            $table->string('forma', 20)->nullable();
            $table->string('diametroMatriz', 255)->nullable();
            $table->double('anchoPerfil')->nullable()->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perfil');
    }
};
