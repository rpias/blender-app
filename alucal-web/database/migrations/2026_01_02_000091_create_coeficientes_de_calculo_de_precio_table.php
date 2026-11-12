<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDConfiguracion.s3db / tabla original "CoeficientesDeCalculoDePrecio".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coeficientes_de_calculo_de_precio', function (Blueprint $table) {
            $table->string('clave')->unique();
            $table->string('valor', 10);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coeficientes_de_calculo_de_precio');
    }
};
