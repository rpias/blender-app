<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDDefinicionesAberturas.s3db / tabla original "divisoresParametro".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('divisores_parametro', function (Blueprint $table) {
            $table->id();
            $table->integer('idDefinicionParametro');
            $table->string('divisor', 16);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('divisores_parametro');
    }
};
