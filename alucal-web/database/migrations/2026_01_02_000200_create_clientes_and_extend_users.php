<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Clientes unificados.
 *
 * En la app Java convivían dos definiciones incompatibles:
 *  - BDAlucal.Cliente   (nombre, telCel, direccion, email)      -> datos reales en uso
 *  - BDClientes.Cliente (nroCliente, nombreEmpresa, telefono...) -> estructura más rica, sin datos
 * Aquí se conserva una sola tabla que acepta ambas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('numero')->nullable()->unique();
            $table->string('nombre', 160);
            $table->string('empresa', 160)->nullable();
            $table->string('telefono', 40)->nullable();
            $table->string('email', 120)->nullable();
            $table->string('direccion', 160)->nullable();
            $table->string('alias', 60)->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('nombre', 120)->nullable()->after('name');
            $table->string('rol', 20)->default('vendedor')->after('nombre');
            $table->boolean('activo')->default(true)->after('rol');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nombre', 'rol', 'activo']);
        });

        Schema::dropIfExists('clientes');
    }
};
