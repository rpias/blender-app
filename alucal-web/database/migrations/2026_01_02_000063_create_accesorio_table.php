<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAccesorios.s3db / tabla original "Accesorio".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accesorio', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->nullable();
            $table->string('descripcion', 255)->nullable();
            $table->integer('precio')->nullable();
            $table->string('unidad', 10)->nullable();
            $table->integer('cantPrecio')->nullable();
            $table->string('comentario', 255)->nullable();
            $table->string('codigoAlcan', 10)->nullable();
            $table->string('codigoTanit', 10)->nullable();
            $table->string('codigoSapa', 10)->nullable();
            $table->integer('idCategoria');
            $table->string('palabrasClave', 255)->nullable();
            $table->integer('idMaterial')->nullable();
            $table->boolean('activo')->nullable();
            $table->boolean('perteneceAListadoAccImportados');
            $table->integer('stockMinimo');
            $table->integer('reposicion');
            $table->double('cantHorasColocacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accesorio');
    }
};
