<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un suceso registrado: quién hizo qué, cuándo y desde dónde (spec 0012).
 */
class RegistroSuceso extends Model
{
    protected $table = 'registro_sucesos';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'datos' => 'array',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
