<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class mantenimiento_archivos extends Model
{
    use HasFactory;

    //
    protected $table = 'mantenimiento_archivos';
    protected $fillable = [
        'mantenimiento_id',
        'nombre_archivo',
        'ruta_archivo',
    ];

    public function mantenimiento(): BelongsTo
    {
        return $this->belongsTo(mantenimiento::class, 'mantenimiento_id');
    }
}
