<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAccesorios.s3db / tabla original "LugarDeUsoAccesorio".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lugar_de_uso_accesorio', function (Blueprint $table) {
            $table->integer('idAccesorio');
            $table->string('tipologia', 40)->nullable();
            $table->string('serie', 40)->nullable();
            $table->string('parte', 40);
            $table->integer('idTipologia')->nullable();
            $table->integer('idSerie')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lugar_de_uso_accesorio');
    }
};
