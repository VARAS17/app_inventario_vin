<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MobiliarioAsignacion extends Model
{
    use HasFactory;

    protected $table = 'mobiliarios_asignaciones';

    protected $fillable = [
        'mobiliario_id',
        'personal_id',
        'area_origen_id',
        'area_destino_id',
        'fecha_traspaso',
    ];

    protected $casts = [
        'fecha_traspaso' => 'date',
    ];

    // Mueble transferido
    public function mobiliario(): BelongsTo
    {
        return $this->belongsTo(Mobiliario::class, 'mobiliario_id');
    }

    // Custodio individual (opcional: null para áreas comunes)
    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'personal_id');
    }

    // Área desde donde salió
    public function areaOrigen(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_origen_id');
    }

    // Área que lo recibe (física)
    public function areaDestino(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_destino_id');
    }

    // Evidencias adjuntas a esta transferencia
    public function archivos(): HasMany
    {
        return $this->hasMany(MobiliarioAsignacionArchivo::class, 'asignacion_id');
    }
}