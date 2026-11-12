<?php

namespace App\Servicios\Catalogos;

/**
 * Descriptor de un catálogo editable.
 *
 * Un único controlador y una única vista sirven a todos los catálogos: acá se
 * declara la tabla, qué columnas se editan, cómo se valida y cuál es la clave.
 */
class RecursoCatalogo
{
    /**
     * @param  list<array{campo: string, etiqueta: string, tipo: string}>  $columnas
     * @param  array<string, string>  $reglas
     * @param  list<string>  $busqueda
     * @param  array<string, string>  $orden
     * @param  list<string>  $claves  columnas que identifican una fila
     */
    public function __construct(
        public readonly string $clave,
        public readonly string $titulo,
        public readonly string $tabla,
        public readonly array $columnas,
        public readonly array $reglas,
        public readonly array $claves,
        public readonly array $busqueda = [],
        public readonly array $orden = ['id' => 'asc'],
        public readonly int $porPagina = 50,
        public readonly array $camposCrear = [],
        public readonly array $reglasCrear = [],
        public readonly bool $permiteEliminar = false,
        public readonly array $porDefecto = [],
    ) {}

    /**
     * @return list<string>
     */
    public function camposEditables(): array
    {
        $campos = [];

        foreach ($this->columnas as $columna) {
            if ($columna['tipo'] !== 'solo-lectura') {
                $campos[] = $columna['campo'];
            }
        }

        return $campos;
    }

    public function permiteCrear(): bool
    {
        return $this->camposCrear !== [];
    }

    /**
     * Identificador estable de una fila para el formulario.
     */
    public function identificadorDe(object $fila): string
    {
        $partes = [];

        foreach ($this->claves as $columna) {
            $partes[] = (string) ($fila->{$columna} ?? '');
        }

        return implode(':', $partes);
    }

    /**
     * Condiciones `where` a partir del identificador del formulario.
     *
     * @return array<string, string>
     */
    public function condiciones(string $identificador): array
    {
        $partes = explode(':', $identificador);

        if (count($partes) !== count($this->claves)) {
            return [];
        }

        return array_combine($this->claves, $partes);
    }
}
