<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDAccesorios.s3db / tabla original "ProveedorAccesorios".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proveedor_accesorios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedor_accesorios');
    }
};
