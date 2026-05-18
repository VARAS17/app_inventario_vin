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
        'personal_id' // Cambiado de user_id a personal_id
    ];

    /**
     * Obtener el responsable del mueble (Relación inversa).
     */
    public function personal(): BelongsTo
    {
        // Ahora pertenece al modelo Personal
        return $this->belongsTo(Personal::class);
    }
}