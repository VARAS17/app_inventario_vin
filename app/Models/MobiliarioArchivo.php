<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MobiliarioArchivo extends Model
{
    use HasFactory;

    protected $table = 'mobiliarios_archivos';

    protected $fillable = [
        'mobiliario_id',
        'nombre_archivo',
        'ruta_archivo',
    ];

    public function mobiliario(): BelongsTo
    {
        return $this->belongsTo(Mobiliario::class, 'mobiliario_id');
    }
}