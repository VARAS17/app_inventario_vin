<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobiliarioAsignacionArchivo extends Model
{
    use HasFactory;

    protected $table = 'mobiliarios_asignaciones_archivos';

    protected $fillable = [
        'asignacion_id',
        'nombre_archivo',
        'ruta_archivo',
    ];

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(MobiliarioAsignacion::class, 'asignacion_id');
    }
}