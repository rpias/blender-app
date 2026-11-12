<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDVidrios.s3db / tabla original "Revestimiento".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revestimiento', function (Blueprint $table) {
            $table->string('idVidrioSinCamara')->unique();
            $table->integer('espesorEnMM');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revestimiento');
    }
};
