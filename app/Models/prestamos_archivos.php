<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class prestamos_archivos extends Model
{
    use HasFactory;

    //
    protected $table = 'prestamos_archivos';
    protected $fillable = [
        'prestamo_id',
        'nombre_archivo',
        'ruta_archivo',
    ];

    public function prestamo(): BelongsTo
    {
        return $this->belongsTo(prestamos::class, 'prestamo_id');
    }
}
