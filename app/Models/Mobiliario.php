<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mobiliario extends Model
{
    /**
     * Campos permitidos para asignación masiva.
     */
    protected $fillable = [
        'nombre',
        'material',
        'color',
        'estado',
        'lugar',
        'user_id'
    ];

    /**
     * Obtener el responsable del mueble (Relación inversa).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}