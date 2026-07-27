<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tecnologia extends Model
{
    /**
     * Campos permitidos para asignación masiva.
     */
    // Dentro de app/Models/Tecnologia.php
    protected $table = 'tecnologias';
    protected $fillable = [
        'nombre',
        'marca',
        'serie',
        'lugar',
        'estado',
        'personal_id' // Cambiado de user_id a personal_id
    ];

    /**
     * Obtener el personal al que está asignado el equipo (Relación inversa).
     */
    public function personal(): BelongsTo
    {
        // Ahora pertenece al modelo Personal
        return $this->belongsTo(Personal::class);
    }
}