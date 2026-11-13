<?php

namespace App\Services\Aberturas;

use App\Models\Accesorio;

/**
 * Mosquitero fijo, portado de AberturaService.agregarMosquiteroFijo
 * (AberturaService.java:3179-3203) y de agregarTejidoMosquitero
 * (AberturaServiceCorrediza.java:10427-10446).
 *
 * Se agrega a una abertura ya calculada: la hoja de mosquitero va 5 mm menor
 * que el vano y el tejido se elige por el lado menor.
 */
class Mosquitero
{
    /** Código de perfil de mosquitero según la serie. */
    public const PERFIL_S20 = '214';

    public const PERFIL_DOMINA = '6255';

    private const ESCUADRA = [
        '214' => '230661',
        '6255' => '230428',
    ];

    /** Tejido por lado menor del mosquitero (mm). */
    private const TEJIDO = [
        800 => '230861',
        1000 => '230862',
        1200 => '230863',
        1500 => '230864',
        1800 => '230865',
        2500 => '232001',
    ];

    public function __construct(private readonly Constantes $constantes) {}

    public function agregar(LineasAbertura $lineas, string $codigoPerfil, float $anchoMM, float $altoMM, int $cantidad = 1): void
    {
        $escuadra = self::ESCUADRA[$codigoPerfil] ?? throw new SerieSinConstantes("El perfil '{$codigoPerfil}' no es un mosquitero válido.");

        $peso = $this->constantes->pesoPerfil($codigoPerfil);

        $lineas->agregarBarra($codigoPerfil, $peso, 2 * $cantidad, $anchoMM - 5, 'MOSQUITERO');
        $lineas->agregarBarra($codigoPerfil, $peso, 2 * $cantidad, $altoMM - 5, 'MOSQUITERO');

        $anchoM = $anchoMM / 1000.0;
        $altoM = $altoMM / 1000.0;

        $this->accesorio($lineas, $escuadra, 4.0 * $cantidad);
        $this->accesorio($lineas, '230623', 2.0 * ($anchoM + $altoM) * $cantidad);

        $minimo = min($anchoMM, $altoMM);
        $maximo = max($anchoMM, $altoMM);
        $tejido = null;

        foreach (self::TEJIDO as $limite => $codigo) {
            if ($minimo < $limite) {
                $tejido = $codigo;
                break;
            }
        }

        if ($tejido === null) {
            throw new SerieSinConstantes("El mosquitero mide {$anchoMM} × {$altoMM} mm y el rollo más ancho es de 2.500 mm.");
        }

        $this->accesorio($lineas, $tejido, ($maximo / 1000.0) * $cantidad);
        $this->accesorio($lineas, '196150', 2.0 * ($anchoM + $altoM) * $cantidad);
    }

    private function accesorio(LineasAbertura $lineas, string $codigo, float $cantidad): void
    {
        if ($cantidad <= 0) {
            return;
        }

        $accesorio = Accesorio::query()->where('codigo', $codigo)->first();

        if ($accesorio === null) {
            throw new SerieSinConstantes("Falta el accesorio '{$codigo}' en el catálogo.");
        }

        $cantPrecio = (int) $accesorio->cantPrecio;
        $precioUnitario = $cantPrecio > 0
            ? (float) $accesorio->precio / $cantPrecio / 100
            : (float) $accesorio->precio;

        $lineas->accesorios[] = [
            'codigo' => $codigo,
            'precioUnitario' => $precioUnitario,
            'cantidad' => round($cantidad, 3),
            'parte' => 'MOSQUITERO',
        ];
    }
}
