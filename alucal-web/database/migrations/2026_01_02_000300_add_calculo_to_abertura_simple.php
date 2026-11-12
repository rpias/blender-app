<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Snapshot de cálculo por abertura.
 *
 * El original Java no persiste las cantidades de una abertura simple: las
 * recalcula al abrir la obra (DaoAberturaSimple.java:223). Acá se guarda el
 * resultado del cálculo para que el presupuesto sea reproducible y auditable,
 * más las constantes de corte que en el original están hardcodeadas por serie.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('abertura_simple', function (Blueprint $table) {
            $table->json('parametros')->nullable()->after('idRellenoVano');
            $table->json('lineas')->nullable()->after('parametros');
            $table->integer('precioUnitario')->nullable()->after('lineas');
            $table->dateTime('calculadoEn')->nullable()->after('precioUnitario');
        });

        // Constantes de corte por tipología-serie (antes hardcodeadas en Java).
        Schema::create('constantes_calculo', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('idTipologia');
            $table->unsignedInteger('idSerie');
            $table->string('clave', 60);
            $table->double('valor');
            $table->unique(['idTipologia', 'idSerie', 'clave']);
        });

        // Medidas de perfil que el catálogo no trae (ancho de contravidrio/divisor).
        Schema::create('perfil_medidas', function (Blueprint $table) {
            $table->string('codigo', 30)->primary();
            $table->double('anchoMM');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perfil_medidas');
        Schema::dropIfExists('constantes_calculo');

        Schema::table('abertura_simple', function (Blueprint $table) {
            $table->dropColumn(['parametros', 'lineas', 'precioUnitario', 'calculadoEn']);
        });
    }
};
