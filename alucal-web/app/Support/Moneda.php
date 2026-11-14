<?php

namespace App\Support;

/**
 * Formato de moneda de la aplicación (spec 0013).
 *
 * Los importes se muestran siempre con dos decimales, separador de miles con
 * punto y decimal con coma: 1.882,00.
 */
class Moneda
{
    public const SIMBOLO = 'U$S';

    public static function formatear(mixed $valor): string
    {
        return number_format((float) ($valor ?? 0), 2, ',', '.');
    }

    public static function conSimbolo(mixed $valor): string
    {
        return self::SIMBOLO.' '.self::formatear($valor);
    }
}
