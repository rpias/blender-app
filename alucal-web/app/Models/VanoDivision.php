<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * División de un vano compuesto: dónde va el conector y con qué perfil.
 */
class VanoDivision extends Model
{
    protected $table = 'vano_division';

    protected $guarded = [];

    public function vano(): BelongsTo
    {
        return $this->belongsTo(ElementoObra::class, 'idElementoVano');
    }
}
