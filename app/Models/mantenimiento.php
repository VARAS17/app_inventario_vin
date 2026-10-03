<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mantenimiento extends Model
{
    use HasFactory;

    protected $table = 'mantenimientos';

    protected $fillable = [
        'tecnologia_id',
        'area_origen_id',      // Corregido (tenía un cero)
        'tipo',
        'taller_proveedor',
        'motivo',
        'fecha_envio',
        'estado_previo',
        'fecha_ingreso',
        'solucion',
        'quedo_operativo',
    ];

    /**
     * Conversión automática de tipos de datos
     */
    protected $casts = [
        'fecha_envio'     => 'date',
        'fecha_ingreso'   => 'date',
        'quedo_operativo' => 'boolean',
    ];

    /**
     * Área de donde provino el equipo a reparar
     */
    public function areaOrigen(): BelongsTo
    {
        return $this->belongsTo(Area::class, 'area_origen_id');
    }

    /**
     * Equipo enviado a mantenimiento
     */
    public function tecnologia(): BelongsTo
    {
        return $this->belongsTo(Tecnologia::class, 'tecnologia_id');
    }

    /**
     * Informes técnicos, garantías o comprobantes adjuntos
     */
    public function archivos(): HasMany
    {
        return $this->hasMany(mantenimiento_archivos::class, 'mantenimiento_id');
    }
}