<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAlucal.s3db / tabla original "Recordatorio".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recordatorio', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('texto', 255);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recordatorio');
    }
};
