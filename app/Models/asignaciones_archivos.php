<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class asignaciones_archivos extends Model
{
    use HasFactory;

    //
    protected $table = 'asignaciones_archivos';
    protected $fillable = [
        'asginacion_id',
        'nombre_archivo',
        'ruta_archivo',
    ];

    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(asignaciones::class, 'asignacion_id');
    }
}
