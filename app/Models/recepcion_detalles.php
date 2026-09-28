<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class recepcion_detalles extends Model
{
    use HasFactory;

    protected $table = 'recepcion_detalles';

    protected $fillable = [
        'recepcion_id',
        'util_id',
        'cantidad_recibida',
    ];

    /**
     * Relación: Pertenece a una recepción/entrega específica de Abastecimiento.
     */
    public function recepcion(): BelongsTo
    {
        return $this->belongsTo(recepciones_abastecimientos::class, 'recepcion_id');
    }

    /**
     * Relación: Es un útil del catálogo.
     */
    public function util(): BelongsTo
    {
        return $this->belongsTo(utiles::class, 'util_id');
    }
}