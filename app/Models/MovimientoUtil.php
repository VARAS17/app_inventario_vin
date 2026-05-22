<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MovimientoUtil extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'movimientos_utiles';

    /**
     * Campos que se pueden llenar masivamente
     */
    protected $fillable = [
        'util_id',
        'tipo',
        'cantidad',
        'descripcion',
        'fecha',
    ];

    /**
     * Relación: Un movimiento pertenece a un Útil.
     */
    public function util()
    {
        return $this->belongsTo(Util::class, 'util_id');
    }
}