<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAlucal.s3db / tabla original "VanoDividido".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vano_dividido', function (Blueprint $table) {
            $table->id();
            $table->integer('ancho');
            $table->integer('alto');
            $table->boolean('llevaConector');
            $table->string('codigoConector', 16);
            $table->string('terminacionConector', 40);
            $table->boolean('esDivisionHorizontal');
            $table->integer('posicionDivision');
            $table->integer('idHijo1');
            $table->integer('idHijo2');
            $table->integer('idRellenoVano');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vano_dividido');
    }
};
