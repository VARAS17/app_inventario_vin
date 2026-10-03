<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MobiliarioSalida extends Model
{
    use HasFactory;

    protected $table = 'mobiliarios_salidas';

    protected $fillable = [
        'mobiliario_id',
        'area_origen_id',
        'tipo_baja',
        'destino_final',
        'responsable_recepcion',
        'fecha_salida',
    ];

    protected $casts = [
        'fecha_salida' => 'date',
    ];

    // Mueble dado de baja
    public function mobiliario(): BelongsTo
    {
        return $this->belongsTo(Mobiliario::class, 'mobiliario_id');
    }

    // Área desde donde salió
    public function areaOrigen(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_origen_id');
    }

    // Evidencias del desecho / baja
    public function archivos(): HasMany
    {
        return $this->hasMany(MobiliarioSalidaArchivo::class, 'salida_id');
    }
}