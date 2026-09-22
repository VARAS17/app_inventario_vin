<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class asignaciones extends Model
{
    use HasFactory;

    protected $table = 'asignaciones';

    protected $fillable = [
        'tecnologia_id',
        'personal_id',
        'area_origen_id',
        'area_destino_id',
        'fecha_traspaso',
    ];

    // Relación con el Área de donde sale el equipo
    public function areaOrigen(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_origen_id');
    }

    // Relación con el Área que recibe el equipo
    public function areaDestino(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_destino_id');
    }

    // Relación con el custodio (personal)
    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'personal_id');
    }

    // Relación con el equipo asignado
    public function tecnologia(): BelongsTo
    {
        return $this->belongsTo(Tecnologia::class, 'tecnologia_id');
    }

    // Relación con las actas/archivos adjuntos
    public function archivos(): HasMany
    {
        return $this->hasMany(asignaciones_archivos::class, 'asignacion_id');
    }
}