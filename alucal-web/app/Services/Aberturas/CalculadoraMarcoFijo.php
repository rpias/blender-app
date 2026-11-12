<?php

namespace App\Services\Aberturas;

/**
 * Marco fijo rectangular con contravidrios, divisores y un vidrio por paño.
 *
 * Port de AberturaServiceMarcoFijo (marco 45-45 + acristalamiento genérico):
 *  - 2 barras horizontales y 2 verticales de marco;
 *  - contravidrios 2H + 2V por paño;
 *  - divisores horizontales/verticales;
 *  - un vidrio por paño, con el ancho del divisor más 6 mm de juego.
 *
 * Referencias: Alucal_v5.5/_analysis/aberturas.md §3.1 y
 * AberturaService.agregarAcristalamiento...MarcoFijo (AberturaService.java:2391-2449).
 */
class CalculadoraMarcoFijo
{
    public function __construct(private readonly Constantes $constantes) {}

    /**
     * @param  array{
     *     anchoMM: float,
     *     altoMM: float,
     *     idSerie: int,
     *     perfilMarco: string,
     *     perfilContravidrio?: string|null,
     *     perfilDivisor?: string|null,
     *     divisoresHorizontales?: int,
     *     divisoresVerticales?: int,
     *     vidrio?: array{costoM2: float, precioMinimo: float, margen: float}|null,
     *     cantidad?: int
     * }  $datos
     */
    public function calcular(array $datos): LineasAbertura
    {
        $idTipologia = self::TIPOLOGIA;
        $idSerie = $datos['idSerie'];
        $cantidad = max(1, (int) ($datos['cantidad'] ?? 1));

        $ancho = (float) $datos['anchoMM'];
        $alto = (float) $datos['altoMM'];

        // El descuento de marco se aplica al largo de la barra (nominal por defecto).
        $largoMarcoH = $ancho - $this->constantes->de($idTipologia, $idSerie, 'marcoAnchoDescuentoMM');
        $largoMarcoV = $alto - $this->constantes->de($idTipologia, $idSerie, 'marcoAltoDescuentoMM');

        $lineas = new LineasAbertura;
        $pesoMarco = $this->constantes->pesoPerfil($datos['perfilMarco']);

        // Marco: 2 horizontales (corte 45-45) + 2 verticales, por unidad.
        $lineas->agregarBarra($datos['perfilMarco'], $pesoMarco, 2 * $cantidad, $largoMarcoH, 'MARCO');
        $lineas->agregarBarra($datos['perfilMarco'], $pesoMarco, 2 * $cantidad, $largoMarcoV, 'MARCO');

        $divHor = max(0, (int) ($datos['divisoresHorizontales'] ?? 0));
        $divVer = max(0, (int) ($datos['divisoresVerticales'] ?? 0));
        $gap = $this->constantes->de($idTipologia, $idSerie, 'divisorGapMM', 6.0);

        $perfilContravidrio = $datos['perfilContravidrio'] ?? null;
        if ($perfilContravidrio !== null) {
            $largoContravH = $ancho - $this->constantes->de($idTipologia, $idSerie, 'contravidrioAnchoDescuentoMM');
            $largoContravV = $alto - $this->constantes->de($idTipologia, $idSerie, 'contravidrioAltoDescuentoMM');
            $pesoContrav = $this->constantes->pesoPerfil($perfilContravidrio);

            $anchoDivisorVer = $divVer > 0 && ($datos['perfilDivisor'] ?? null)
                ? $this->constantes->anchoPerfil((string) $datos['perfilDivisor'])
                : 0.0;
            $anchoDivisorHor = $divHor > 0 && ($datos['perfilDivisor'] ?? null)
                ? $this->constantes->anchoPerfil((string) $datos['perfilDivisor'])
                : 0.0;

            $cantContravH = (1 + $divHor) * 2 * (1 + $divVer);
            $cantContravV = (1 + $divVer) * 2 * (1 + $divHor);

            $largoContravH = ($largoContravH - $anchoDivisorVer * $divVer) / ($divVer + 1);
            $largoContravV = ($largoContravV - $anchoDivisorHor * $divHor) / ($divHor + 1);

            $lineas->agregarBarra($perfilContravidrio, $pesoContrav, $cantContravH * $cantidad, $largoContravH, 'MARCO');
            $lineas->agregarBarra($perfilContravidrio, $pesoContrav, $cantContravV * $cantidad, $largoContravV, 'MARCO');

            // Divisores: uno por cada línea de división.
            if ($divVer > 0 && ($datos['perfilDivisor'] ?? null)) {
                $largoDivisorV = ($alto - $this->constantes->de($idTipologia, $idSerie, 'contravidrioAltoDescuentoMM')) / ($divHor + 1);
                $lineas->agregarBarra($datos['perfilDivisor'], $this->constantes->pesoPerfil($datos['perfilDivisor']), $divVer * $cantidad, $largoDivisorV, 'MARCO');
            }

            if ($divHor > 0 && ($datos['perfilDivisor'] ?? null)) {
                $largoDivisorH = ($ancho - $this->constantes->de($idTipologia, $idSerie, 'contravidrioAnchoDescuentoMM')) / ($divVer + 1);
                $lineas->agregarBarra($datos['perfilDivisor'], $this->constantes->pesoPerfil($datos['perfilDivisor']), $divHor * $cantidad, $largoDivisorH, 'MARCO');
            }
        }

        // Vidrios: un paño por celda, en metros, con el ancho del divisor + juego.
        $vidrio = $datos['vidrio'] ?? null;
        if ($vidrio !== null) {
            $anchoVidrioM = $this->redondearA3(($ancho - ($this->anchoDivisor($datos) + $gap) * $divVer) / 1000.0 / ($divVer + 1));
            $altoVidrioM = $this->redondearA3(($alto - ($this->anchoDivisor($datos) + $gap) * $divHor) / 1000.0 / ($divHor + 1));

            $lineas->agregarVidrio(
                cantidad: (1 + $divHor) * (1 + $divVer) * $cantidad,
                anchoM: $anchoVidrioM,
                altoM: $altoVidrioM,
                costoM2: (float) $vidrio['costoM2'],
                precioMinimo: (float) $vidrio['precioMinimo'],
                margen: (float) $vidrio['margen'],
            );
        }

        return $lineas;
    }

    private function anchoDivisor(array $datos): float
    {
        $perfil = $datos['perfilDivisor'] ?? null;

        return $perfil !== null ? $this->constantes->anchoPerfil((string) $perfil) : 0.0;
    }

    private function redondearA3(float $valor): float
    {
        return round($valor, 3);
    }

    public const TIPOLOGIA = 6;
}
