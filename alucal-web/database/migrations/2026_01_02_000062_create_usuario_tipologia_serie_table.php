<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDDefinicionesAberturas.s3db / tabla original "usuario_tipologia_serie".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario_tipologia_serie', function (Blueprint $table) {
            $table->id();
            $table->integer('id_usuario');
            $table->integer('id_tipologia');
            $table->integer('id_serie');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_tipologia_serie');
    }
};
