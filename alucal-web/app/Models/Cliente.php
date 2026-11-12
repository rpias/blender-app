<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Clientes de la carpintería (fusión de BDAlucal.Cliente y BDClientes.Cliente).
 *
 * @property mixed $id
 * @property mixed $numero
 * @property mixed $nombre
 * @property mixed $empresa
 * @property mixed $telefono
 * @property mixed $email
 * @property mixed $direccion
 * @property mixed $alias
 * @property mixed $notas
 */
class Cliente extends Model
{
    protected $table = 'clientes';

    protected $guarded = [];

    /** @return HasMany<Obra, $this> */
    public function obras(): HasMany
    {
        return $this->hasMany(Obra::class, 'cliente', 'nombre');
    }

    public function getNombreCompletoAttribute(): string
    {
        return $this->empresa ? "{$this->nombre} ({$this->empresa})" : (string) $this->nombre;
    }
}
