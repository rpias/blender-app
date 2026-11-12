<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAlucal.s3db / tabla original "ActorNegocio".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actor_negocio', function (Blueprint $table) {
            $table->id();
            $table->integer('idNegocio');
            $table->string('nombre', 50);
            $table->string('celular', 50);
            $table->string('email', 50);
            $table->string('posicion', 50);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actor_negocio');
    }
};
