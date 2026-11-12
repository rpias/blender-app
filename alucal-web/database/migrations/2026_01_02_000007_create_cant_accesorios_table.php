<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAlucal.s3db / tabla original "CantAccesorios".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cant_accesorios', function (Blueprint $table) {
            $table->id();
            $table->integer('idEstructura');
            $table->integer('idValorParametroParticular');
            $table->string('accesorio', 14);
            $table->double('cantidad');
            $table->string('parte', 20);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cant_accesorios');
    }
};
