<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registro de sucesos (spec 0012): auditoría de lo que se hace en el sistema.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registro_sucesos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('usuario_id')->nullable()->index();
            $table->string('usuario_nombre', 120)->nullable();
            $table->string('accion', 40)->index();
            $table->string('entidad', 60)->nullable()->index();
            $table->unsignedInteger('entidad_id')->nullable();
            $table->string('descripcion', 255);
            $table->json('datos')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registro_sucesos');
    }
};
