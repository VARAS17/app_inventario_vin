<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class requerimientos_anuales extends Model
{
    use HasFactory;

    protected $table = 'requerimientos_anuales';

    protected $fillable = [
        'anio',
        'codigo',
        'documento_path',
        'estado',
        'observaciones',
    ];

    /**
     * Relación: Un requerimiento anual contiene muchos útiles solicitados.
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(requerimientos_detalles::class, 'requerimiento_anual_id');
    }

    /**
     * Relación: Un requerimiento anual recibe múltiples entregas parciales de abastecimiento.
     */
    public function recepciones(): HasMany
    {
        return $this->hasMany(recepciones_abastecimientos::class, 'requerimiento_anual_id');
    }
}