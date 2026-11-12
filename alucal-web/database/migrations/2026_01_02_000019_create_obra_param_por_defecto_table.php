<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAlucal.s3db / tabla original "ObraParamPorDefecto".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obra_param_por_defecto', function (Blueprint $table) {
            $table->string('idObra');
            $table->string('terminacion', 20)->nullable();
            $table->boolean('llevaPremarco');
            $table->string('codigoPremarco', 15)->nullable();
            $table->boolean('llevaTapajuntas');
            $table->boolean('llevaMosquitero');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obra_param_por_defecto');
    }
};
