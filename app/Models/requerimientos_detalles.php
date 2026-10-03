<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class requerimientos_detalles extends Model
{
    use HasFactory;

    protected $table = 'requerimiento_detalles';

    protected $fillable = [
        'requerimiento_anual_id',
        'util_id',
        'cantidad_solicitada',
    ];

    /**
     * Relación: Pertenece a un requerimiento anual.
     */
    public function requerimientoAnual(): BelongsTo
    {
        return $this->belongsTo(requerimientos_anuales::class, 'requerimiento_anual_id');
    }

    /**
     * Relación: Es un útil específico del catálogo.
     */
    public function util(): BelongsTo
    {
        return $this->belongsTo(utiles::class, 'util_id');
    }
}