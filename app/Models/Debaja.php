<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Debaja extends Model
{
    use HasFactory;

    protected $table = 'debajas';

    // Campos que permitimos llenar masivamente
    protected $fillable = [
        'nombre',
        'tipo_inventario',
        'motivo',
        'fecha_baja',
        'personal_id',
        'detalles',
        'imagen',
    ];

    /**
     * IMPORTANTE: Casts
     * Esto convierte el campo 'detalles' de JSON a un Array de PHP automáticamente
     * y viceversa. Así no tienes que pelear con formatos.
     */
    protected $casts = [
        'detalles' => 'array',
        'fecha_baja' => 'date',
    ];

    // Relación con el personal (para saber quién lo entregó malogrado)
    public function personal()
    {
        return $this->belongsTo(Personal::class);
    }
}