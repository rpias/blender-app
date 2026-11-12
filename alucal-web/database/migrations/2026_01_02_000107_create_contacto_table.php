<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen: BDClientes.s3db / tabla original "Contacto".
 * Generado por tools/gen_schema.py el 2026-11-11.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacto', function (Blueprint $table) {
            $table->id();
            $table->integer('idCliente');
            $table->string('nombre', 40);
            $table->string('celular', 20);
            $table->string('email', 40);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacto');
    }
};
