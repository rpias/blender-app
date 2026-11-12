<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAlucal.s3db / tabla original "RellenoVano".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('relleno_vano', function (Blueprint $table) {
            $table->id();
            $table->string('tipoRellenoVano', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relleno_vano');
    }
};
