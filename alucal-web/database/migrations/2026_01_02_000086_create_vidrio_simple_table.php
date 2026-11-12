<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDVidrios.s3db / tabla original "VidrioSimple".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vidrio_simple', function (Blueprint $table) {
            $table->string('idVidrioSinCamara')->nullable()->unique();
            $table->integer('espesorEnMM');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vidrio_simple');
    }
};
