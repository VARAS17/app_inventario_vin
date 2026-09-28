<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class movimientos_utiles extends Model
{
    //
        protected $table = 'movimientos_utiles';

    protected $fillable = [
        'util_id',
        'tipo',
        'cantidad',
        'stock_anterior',
        'stock_nuevo',
        'referencia_tipo',
        'referencia_id',
        'descripcion',
        'fecha',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    /**
     * Relación: Pertenece a un útil del catálogo.
     */
    public function util(): BelongsTo
    {
        return $this->belongsTo(utiles::class, 'util_id');
    }
}
