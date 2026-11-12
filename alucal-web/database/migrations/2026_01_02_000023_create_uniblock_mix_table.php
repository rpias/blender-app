<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAlucal.s3db / tabla original "UniblockMix".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('uniblock_mix', function (Blueprint $table) {
            $table->id();
            $table->integer('anchoEnMM');
            $table->integer('altoEnMM');
            $table->string('cajon', 20);
            $table->string('cortina', 20);
            $table->integer('idHijo');
            $table->integer('idRellenoVano')->nullable();
            $table->string('jamba', 16)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uniblock_mix');
    }
};
