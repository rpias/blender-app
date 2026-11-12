<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Constantes de corte y medidas de perfil que el catálogo no trae.
 *
 * En el original están hardcodeadas una por una dentro de cada método de
 * AberturaService* (p. ej. AberturaServiceMarcoFijo.java:299-308). Acá se
 * declaran como datos, con la serie y la cita del código de origen.
 */
class ConstantesCalculoSeeder extends Seeder
{
    /** @var list<array{0:int,1:int,2:string,3:float,4:string}> */
    private const CONSTANTES = [
        // Corrediza Domina (tipología 3 · serie 2): marca de soporte.
        [3, 2, 'soporte', 1.0, 'AberturaServiceCorrediza.java:79-243'],

        // Corrediza Serie 25 (tipología 3 · serie 20): marca de soporte.
        [3, 20, 'soporte', 1.0, 'AberturaServiceCorrediza.java:7366-7521'],

        // Corrediza HA 62 (tipología 3 · serie 10): marca de soporte.
        [3, 10, 'soporte', 1.0, 'AberturaServiceCorrediza.java:getCorredizaHA62_2Hojas'],

        // Corrediza Serie 20 (tipología 3 · serie 19): marca de soporte.
        [3, 19, 'soporte', 1.0, 'AberturaServiceCorrediza.java:6676-6812'],

        // Marco Fijo (tipología 6) · HA 62 (serie 10)
        [6, 10, 'marcoAnchoDescuentoMM', 0.0, 'AberturaServiceMarcoFijo.java:295-296'],
        [6, 10, 'marcoAltoDescuentoMM', 0.0, 'AberturaServiceMarcoFijo.java:295-296'],
        [6, 10, 'contravidrioAnchoDescuentoMM', 44.0, 'AberturaServiceMarcoFijo.java:299-308'],
        [6, 10, 'contravidrioAltoDescuentoMM', 44.0, 'AberturaServiceMarcoFijo.java:299-308'],
        [6, 10, 'divisorGapMM', 6.0, 'AberturaService.java:2428-2429'],

        // Marco Fijo · Serie 20 (serie 19) — marco 206/209/241 del informe
        [6, 19, 'marcoAnchoDescuentoMM', 0.0, 'AberturaServiceMarcoFijo.java:706-711'],
        [6, 19, 'marcoAltoDescuentoMM', 0.0, 'AberturaServiceMarcoFijo.java:706-711'],
        [6, 19, 'contravidrioAnchoDescuentoMM', 58.0, 'AberturaServiceMarcoFijo.java:706-711'],
        [6, 19, 'contravidrioAltoDescuentoMM', 34.0, 'AberturaServiceMarcoFijo.java:706-711'],
        [6, 19, 'divisorGapMM', 6.0, 'AberturaService.java:2428-2429'],

        // Marco Fijo · Rotonda 640 (serie 15)
        [6, 15, 'marcoAnchoDescuentoMM', 0.0, 'AberturaServiceMarcoFijo.java:559-579'],
        [6, 15, 'marcoAltoDescuentoMM', 0.0, 'AberturaServiceMarcoFijo.java:559-579'],
        [6, 15, 'contravidrioAnchoDescuentoMM', 49.0, 'AberturaServiceMarcoFijo.java:559-579'],
        [6, 15, 'contravidrioAltoDescuentoMM', 49.0, 'AberturaServiceMarcoFijo.java:559-579'],
        [6, 15, 'divisorGapMM', 6.0, 'AberturaService.java:2428-2429'],
    ];

    /** @var array<string, float> ancho de perfil en mm (no está en el catálogo) */
    private const PERFILES = [
        'ADR 916' => 26.0,   // "CM - CONTRAVIDRIO RECTO 26 MM"
        'ADR 919' => 26.0,
        '6221' => 24.0,      // divisor 24 mm (AberturaServiceMarcoFijo.java:384-403)
        '6222' => 34.0,      // divisor 34 mm
        '8930' => 24.0,
        '8935' => 34.0,
    ];

    public function run(): void
    {
        foreach (self::CONSTANTES as [$tipologia, $serie, $clave, $valor, $origen]) {
            DB::table('constantes_calculo')->updateOrInsert(
                ['idTipologia' => $tipologia, 'idSerie' => $serie, 'clave' => $clave],
                ['valor' => $valor],
            );
        }

        foreach (self::PERFILES as $codigo => $ancho) {
            DB::table('perfil_medidas')->updateOrInsert(
                ['codigo' => $codigo],
                ['anchoMM' => $ancho],
            );
        }
    }
}
