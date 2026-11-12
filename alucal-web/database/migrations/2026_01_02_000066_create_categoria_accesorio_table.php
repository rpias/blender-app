<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAccesorios.s3db / tabla original "CategoriaAccesorio".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categoria_accesorio', function (Blueprint $table) {
            $table->integer('id');
            $table->string('nombre', 50);
            $table->string('codigoCategoria', 5)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categoria_accesorio');
    }
};
