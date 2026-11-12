<?php

namespace App\Services\Aberturas;

use App\Models\AberturaSimple;

/**
 * Traduce los parámetros EAV del Java a los parámetros de los calculadores.
 */
class MapeadorParametros
{
    public function __construct(private readonly LectorParametros $lector) {}

    /**
     * @return array<string, mixed>
     */
    public function para(AberturaSimple $abertura): array
    {
        $parametros = $this->lector->para($abertura);

        return match ((int) $abertura->idTipologia) {
            CalculadoraMarcoFijo::TIPOLOGIA => $this->marcoFijo($parametros),
            CalculadoraCorredizaS20::TIPOLOGIA => match ((int) $abertura->idSerie) {
                2 => $this->domina($parametros),
                10 => $this->ha62($parametros),
                20 => $this->s25($parametros),
                default => $this->corrediza($parametros),
            },
            default => [],
        };
    }

    /**
     * @param  list<array{def: int, nombre: string, tipo: string, valor: array<string, mixed>}>  $parametros
     * @return array<string, mixed>
     */
    private function marcoFijo(array $parametros): array
    {
        $marco = $this->lector->buscarPorTipo($parametros, 'PERFIL');
        $contravidrio = $this->lector->buscarPorTipo($parametros, 'CONTRAVIDRIOS');
        $divisores = $this->lector->buscarPorTipo($parametros, 'DIVISORES');
        $acristalamiento = $this->lector->buscarPorTipo($parametros, 'ACRISTALAMIENTO');

        $codigoContravidrio = trim((string) ($contravidrio['valor']['contravidrioHorizontal'] ?? ''));
        $codigoDivisor = trim((string) ($divisores['valor']['divisorVertical'] ?? ''));

        return [
            'perfilMarco' => $this->codigoPerfil($marco['valor'] ?? []),
            'perfilContravidrio' => $codigoContravidrio !== '' ? $codigoContravidrio : null,
            'perfilDivisor' => $codigoDivisor !== '' ? $codigoDivisor : null,
            'divisoresHorizontales' => (int) ($divisores['valor']['cantDivisoresHorizontales'] ?? 0),
            'divisoresVerticales' => (int) ($divisores['valor']['cantDivisoresVerticales'] ?? 0),
            'vidrioId' => $this->vidrioDesdeAcristalamiento($acristalamiento['valor'] ?? []),
        ];
    }

    /**
     * Corrediza Domina: enganche, rueda y acristalamiento.
     *
     * @param  list<array{def: int, nombre: string, tipo: string, valor: array<string, mixed>}>  $parametros
     * @return array<string, mixed>
     */
    private function domina(array $parametros): array
    {
        $enganche = $this->lector->buscar($parametros, 'Tipo Enganche');
        $rueda = $this->lector->buscarPorTipo($parametros, 'ACCESORIO');
        $acristalamiento = $this->lector->buscarPorTipo($parametros, 'ACRISTALAMIENTO');

        return [
            'enganche' => strtoupper(trim((string) ($enganche['valor']['option'] ?? 'SIMPLE'))),
            'ruedaCodigo' => trim((string) ($rueda['valor']['codigoAccesorio'] ?? '193155')),
            'divisorAlMedio' => strtoupper((string) ($acristalamiento['valor']['tipoAcristalamiento'] ?? '')) === 'DIVISOR_AL_MEDIO',
            'espesorVidrio' => 4,
            'vidrioId' => $this->vidrioDesdeAcristalamiento($acristalamiento['valor'] ?? []),
        ];
    }

    /**
     * Corrediza HA 62: marco (45/90 grados), enganche y acristalamiento.
     *
     * @param  list<array{def: int, nombre: string, tipo: string, valor: array<string, mixed>}>  $parametros
     * @return array<string, mixed>
     */
    private function ha62(array $parametros): array
    {
        $marco = $this->lector->buscar($parametros, 'Marco');
        $enganche = $this->lector->buscar($parametros, 'Tipo Enganche');
        $acristalamiento = $this->lector->buscarPorTipo($parametros, 'ACRISTALAMIENTO');

        return [
            'marco' => strtoupper(trim((string) ($marco['valor']['option'] ?? '45 GRADOS'))),
            'enganche' => strtoupper(trim((string) ($enganche['valor']['option'] ?? 'SIMPLE'))),
            'espesorVidrio' => 4,
            'vidrioId' => $this->vidrioDesdeAcristalamiento($acristalamiento['valor'] ?? []),
        ];
    }

    /**
     * Corrediza Serie 25: marcos, hoja, enganche, punzonado y rueda.
     *
     * @param  list<array{def: int, nombre: string, tipo: string, valor: array<string, mixed>}>  $parametros
     * @return array<string, mixed>
     */
    private function s25(array $parametros): array
    {
        $marco = $this->lector->buscar($parametros, 'Marco Lateral');
        $parante = $this->lector->buscar($parametros, 'Parante Lateral');
        $zocalo = $this->lector->buscar($parametros, 'Zócalo');
        $cabezal = $this->lector->buscar($parametros, 'Cabezal');
        $caja = $this->lector->buscar($parametros, 'Caja de Agua');
        $punzonado = $this->lector->buscar($parametros, 'Punzonado');
        $enganche = $this->lector->buscar($parametros, 'Tipo_Enganche_S25');
        $rueda = $this->lector->buscar($parametros, 'Tipo de Rueda');
        $acristalamiento = $this->lector->buscarPorTipo($parametros, 'ACRISTALAMIENTO');
        $dvh = $this->dvhDesdeAcristalamiento($acristalamiento['valor'] ?? []);

        return [
            'marcoLateral' => $this->codigoPerfil($marco['valor'] ?? [], '2501'),
            'paranteLateral' => $this->codigoPerfil($parante['valor'] ?? [], '2544'),
            'zocalo' => $this->codigoPerfil($zocalo['valor'] ?? [], '4503'),
            'cabezal' => $this->codigoPerfil($cabezal['valor'] ?? [], '4503'),
            'cajaDeAgua' => strtoupper(trim((string) ($caja['valor']['option'] ?? 'NO'))) === 'SI',
            'punzonadoManual' => strtoupper(trim((string) ($punzonado['valor']['option'] ?? ''))) === 'MANUAL',
            'enganche' => strtoupper(trim((string) ($enganche['valor']['option'] ?? 'SIMPLE'))),
            'enganchePerfil' => '4507',
            'tipoRueda' => strtoupper(trim((string) ($rueda['valor']['option'] ?? 'RULEMAN'))),
            'divisorAlMedio' => strtoupper((string) ($acristalamiento['valor']['tipoAcristalamiento'] ?? '')) === 'DIVISOR_AL_MEDIO',
            'espesorVidrio' => $dvh !== null ? 14 : 4,
            'vidrioId' => $this->vidrioDesdeAcristalamiento($acristalamiento['valor'] ?? []),
            'vidrioDvh' => $dvh,
        ];
    }

    /**
     * @param  list<array{def: int, nombre: string, tipo: string, valor: array<string, mixed>}>  $parametros
     * @return array<string, mixed>
     */
    private function corrediza(array $parametros): array
    {
        $marcoLateral = $this->lector->buscar($parametros, 'Marco Lateral');
        $cabezal = $this->lector->buscar($parametros, 'Cabezal');
        $enganche = $this->lector->buscar($parametros, 'Enganche');
        $zocalo = $this->lector->buscar($parametros, 'ZocaloS20_Rueda');
        $marcoInferior = $this->lector->buscar($parametros, 'Marco_Inferior_Corrediza_S20');
        $punzonado = $this->lector->buscar($parametros, 'Punzonado');
        $acristalamiento = $this->lector->buscarPorTipo($parametros, 'ACRISTALAMIENTO');

        return [
            'marcoLateral' => $this->codigoPerfil($marcoLateral['valor'] ?? []),
            'marcoInferior' => trim((string) ($marcoInferior['valor']['option'] ?? '204')),
            'cajaDeAgua' => false,
            'zocalo' => trim((string) ($zocalo['valor']['option'] ?? '2210')),
            'cabezal' => $this->codigoPerfil($cabezal['valor'] ?? [], '2210'),
            'enganche' => $this->codigoPerfil($enganche['valor'] ?? [], '203'),
            'punzonadoManual' => strtoupper(trim((string) ($punzonado['valor']['option'] ?? ''))) === 'MANUAL',
            'cantEnganches' => 2,
            'divisorAlMedio' => false,
            'vidrioId' => $this->vidrioDesdeAcristalamiento($acristalamiento['valor'] ?? []),
        ];
    }

    /**
     * Código comercial del perfil elegido (columna codigoPerfil en el EAV).
     *
     * @param  array<string, mixed>  $valor
     */
    private function codigoPerfil(array $valor, string $defecto = ''): string
    {
        $codigo = trim((string) ($valor['codigoPerfil'] ?? $valor['perfil'] ?? ''));

        return $codigo !== '' ? $codigo : $defecto;
    }

    /**
     * DVH guardado como "DVH@<exterior>@<camara>@<interior>".
     *
     * @param  array<string, mixed>  $valor
     * @return array{ext: int, camara: int, int: int}|null
     */
    private function dvhDesdeAcristalamiento(array $valor): ?array
    {
        $cristal = (string) ($valor['cristalEntero'] ?? '');

        if (preg_match('/^DVH@(\d+)@(\d+)@(\d+)$/i', $cristal, $m) === 1) {
            return ['ext' => (int) $m[1], 'camara' => (int) $m[2], 'int' => (int) $m[3]];
        }

        return null;
    }

    /**
     * El Java guarda el vidrio como "VIDRIOSINCAMARA@<id>" o "DVH@...".
     *
     * @param  array<string, mixed>  $valor
     */
    private function vidrioDesdeAcristalamiento(array $valor): ?int
    {
        $cristal = (string) ($valor['cristalEntero'] ?? '');

        if (preg_match('/VIDRIOSINCAMARA@(\d+)/i', $cristal, $coincidencia) === 1) {
            return (int) $coincidencia[1];
        }

        return null;
    }
}
