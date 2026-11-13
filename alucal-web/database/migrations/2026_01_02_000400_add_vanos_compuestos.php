<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Aberturas compuestas (spec 0009): un vano agrupa aberturas y cobra conectores.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('elemento_obra', function (Blueprint $table) {
            $table->unsignedInteger('idElementoVano')->nullable()->index();
        });

        Schema::create('vano_division', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('idElementoVano')->index();
            $table->string('orientacion', 10)->default('VERTICAL');
            $table->unsignedInteger('posicionMM')->default(0);
            $table->string('codigoConector', 30);
            $table->string('terminacion', 60)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vano_division');

        Schema::table('elemento_obra', function (Blueprint $table) {
            $table->dropColumn('idElementoVano');
        });
    }
};
