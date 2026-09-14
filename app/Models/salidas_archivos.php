<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class salidas_archivos extends Model
{
    use HasFactory;

    //
    protected $table = 'salidas_archivos';
    protected $fillable = [
        'salidas_id',
        'nombre_archivo',
        'ruta_archivo',
    ];

    public function salida(): BelongsTo
    {
        return $this->belongsTo(salidas::class, 'salida_id');
    }
}
