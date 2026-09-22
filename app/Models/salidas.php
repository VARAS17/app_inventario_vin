<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class salidas extends Model
{
    use HasFactory;

    //
    protected $table = 'salidas';
    protected $fillable = [
        'tecnologia_id',
        'area_origen_id',
        'tipo_baja',
        'destino_final',
        'responsable_recepcion',
        'fecha_salida'

    ];

    protected $casts = [
        'fecha_salida' => 'date',
    ];

    /**
     * Área desde donde salió el bien (Almacén VIN, oficina, etc.)
     */
    public function areaOrigen(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_origen_id');
    }

    public function tecnologia(): BelongsTo
    {
        return $this->belongsTo(tecnologia::class, 'tecnologia_id');
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(salidas_archivos::class, 'salida_id');
    }
}
