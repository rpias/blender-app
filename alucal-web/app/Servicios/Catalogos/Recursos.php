<?php

namespace App\Servicios\Catalogos;

/**
 * Registro de catálogos editables (spec 0006).
 */
class Recursos
{
    /**
     * @return array<string, RecursoCatalogo>
     */
    public function todos(): array
    {
        return [
            'coeficientes' => new RecursoCatalogo(
                clave: 'coeficientes',
                titulo: 'Coeficientes de cálculo',
                tabla: 'coeficientes_de_calculo_de_precio',
                columnas: [
                    ['campo' => 'clave', 'etiqueta' => 'Clave', 'tipo' => 'solo-lectura'],
                    ['campo' => 'valor', 'etiqueta' => 'Valor', 'tipo' => 'texto'],
                ],
                reglas: ['valor' => 'required|numeric'],
                claves: ['clave'],
                orden: ['clave' => 'asc'],
                porPagina: 100,
            ),

            'precios-kilo' => new RecursoCatalogo(
                clave: 'precios-kilo',
                titulo: 'Precio por kilo',
                tabla: 'precios_por_kilo',
                columnas: [
                    ['campo' => 'terminacion', 'etiqueta' => 'Terminación', 'tipo' => 'solo-lectura'],
                    ['campo' => 'rpt', 'etiqueta' => 'RPT', 'tipo' => 'solo-lectura'],
                    ['campo' => 'valor', 'etiqueta' => 'U$S/kg', 'tipo' => 'numero'],
                ],
                reglas: ['valor' => 'required|numeric|min:0'],
                claves: ['id'],
                busqueda: ['terminacion'],
                orden: ['terminacion' => 'asc'],
                porPagina: 100,
            ),

            'margenes' => new RecursoCatalogo(
                clave: 'margenes',
                titulo: 'Márgenes y desperdicios',
                tabla: 'margenes_desperdicios_coef_ajuste',
                columnas: [
                    ['campo' => 'idTipologia', 'etiqueta' => 'Tipología', 'tipo' => 'solo-lectura'],
                    ['campo' => 'idSerie', 'etiqueta' => 'Serie', 'tipo' => 'solo-lectura'],
                    ['campo' => 'MargenMH', 'etiqueta' => 'Margen MH', 'tipo' => 'numero'],
                    ['campo' => 'DespMH', 'etiqueta' => 'Desperdicio MH', 'tipo' => 'numero'],
                    ['campo' => 'MargenMosq', 'etiqueta' => 'Margen mosq.', 'tipo' => 'numero'],
                    ['campo' => 'DespMosq', 'etiqueta' => 'Desperdicio mosq.', 'tipo' => 'numero'],
                ],
                reglas: [
                    'MargenMH' => 'required|numeric|min:1',
                    'DespMH' => 'required|numeric|min:1',
                    'MargenMosq' => 'required|numeric|min:0',
                    'DespMosq' => 'required|numeric|min:0',
                ],
                claves: ['idTipologia', 'idSerie'],
                orden: ['idTipologia' => 'asc', 'idSerie' => 'asc'],
                porPagina: 100,
            ),

            'accesorios' => new RecursoCatalogo(
                clave: 'accesorios',
                titulo: 'Accesorios',
                tabla: 'accesorio',
                columnas: [
                    ['campo' => 'codigo', 'etiqueta' => 'Código', 'tipo' => 'solo-lectura'],
                    ['campo' => 'descripcion', 'etiqueta' => 'Descripción', 'tipo' => 'solo-lectura'],
                    ['campo' => 'precio', 'etiqueta' => 'Precio', 'tipo' => 'numero'],
                    ['campo' => 'cantPrecio', 'etiqueta' => 'Cada N', 'tipo' => 'entero'],
                ],
                reglas: [
                    'precio' => 'required|numeric|min:0',
                    'cantPrecio' => 'required|integer|min:1',
                ],
                claves: ['id'],
                busqueda: ['codigo', 'descripcion'],
                orden: ['codigo' => 'asc'],
            ),

            'perfiles' => new RecursoCatalogo(
                clave: 'perfiles',
                titulo: 'Perfiles',
                tabla: 'perfil',
                columnas: [
                    ['campo' => 'Numero', 'etiqueta' => 'Número', 'tipo' => 'solo-lectura'],
                    ['campo' => 'Descripcion', 'etiqueta' => 'Descripción', 'tipo' => 'texto'],
                    ['campo' => 'Peso', 'etiqueta' => 'kg/m', 'tipo' => 'numero'],
                ],
                reglas: [
                    'Descripcion' => 'required|string|max:255',
                    'Peso' => 'required|numeric|min:0',
                ],
                claves: ['id'],
                busqueda: ['Numero', 'Descripcion'],
                orden: ['Numero' => 'asc'],
            ),

            'vidrios' => new RecursoCatalogo(
                clave: 'vidrios',
                titulo: 'Vidrios',
                tabla: 'vidrio_sin_camara',
                columnas: [
                    ['campo' => 'id', 'etiqueta' => 'ID', 'tipo' => 'solo-lectura'],
                    ['campo' => 'precio', 'etiqueta' => 'U$S/m²', 'tipo' => 'numero'],
                    ['campo' => 'precioMinimo', 'etiqueta' => 'Mínimo', 'tipo' => 'numero'],
                    ['campo' => 'margen', 'etiqueta' => 'Margen', 'tipo' => 'numero'],
                ],
                reglas: [
                    'precio' => 'required|numeric|min:0',
                    'precioMinimo' => 'required|numeric|min:0',
                    'margen' => 'required|numeric|min:1',
                ],
                claves: ['id'],
                orden: ['id' => 'asc'],
            ),
        ];
    }

    public function buscar(string $clave): ?RecursoCatalogo
    {
        return $this->todos()[$clave] ?? null;
    }
}
