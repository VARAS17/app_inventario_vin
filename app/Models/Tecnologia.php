<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tecnologia extends Model
{
    /**
     * Campos permitidos para asignación masiva.
     */
    protected $fillable = [
        'nombre',
        'marca',
        'serie',
        'lugar',
        'estado',
        'user_id'
    ];

    /**
     * Obtener el usuario (personal) al que está asignado el equipo.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}