<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class utiles extends Model
{
    use HasFactory;

    protected $table = 'utiles';

    protected $fillable = [
        'nombre',
        'marca',
        'unidad',
        'stock_actual',
        'stock_minimo',
    ];

    /**
     * Relación: Un útil tiene muchos movimientos en el Kardex.
     */
    public function movimientos(): HasMany
    {
        return $this->hasMany(movimientos_utiles::class, 'util_id');
    }

    /**
     * Relación: Un útil puede estar en múltiples requerimientos anuales.
     */
    public function requerimientoDetalles(): HasMany
    {
        return $this->hasMany(requerimientos_detalles::class, 'util_id');
    }

    /**
     * Relación: Un útil puede recibirse en varias entregas de abastecimiento.
     */
    public function recepcionDetalles(): HasMany
    {
        return $this->hasMany(recepcion_detalles::class, 'util_id');
    }

    /**
     * Relación: Un útil puede entregarse a varios miembros del personal.
     */
    public function entregaDetalles(): HasMany
    {
        return $this->hasMany(entrega_detalle::class, 'util_id');
    }
}