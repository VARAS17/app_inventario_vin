<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// Importamos el modelo del historial (opcional si están en el mismo namespace)
use App\Models\MovimientoUtil; 

class Util extends Model
{
    protected $table = 'utiles';

    protected $fillable = [
        'nombre',
        'marca',
        'cantidad',
        'unidad',
    ];

    /**
     * Relación: Un útil tiene muchos movimientos (historial).
     */
    public function movimientos()
    {
        // 'util_id' es la FK que definimos en la migración del historial
        return $this->hasMany(MovimientoUtil::class, 'util_id');
    }
}