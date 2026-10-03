<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class entrega_detalle extends Model
{
    use HasFactory;

    protected $table = 'entrega_detalles';

    protected $fillable = [
        'entrega_id',
        'util_id',
        'cantidad_entregada',
    ];

    /**
     * Relación: Pertenece a una entrega interna general.
     */
    public function entrega(): BelongsTo
    {
        return $this->belongsTo(entregas_personal::class, 'entrega_id');
    }

    /**
     * Relación: Es un útil del catálogo.
     */
    public function util(): BelongsTo
    {
        return $this->belongsTo(utiles::class, 'util_id');
    }
}