<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class prestamos extends Model
{
    use HasFactory;

    //
    protected $table= 'prestamos';

    protected $fillable = [
        'tecnologias_id',
        'area_origen',
        'area_destino',
        'fecha_prestamo',
        'fecha_devolucion',
    ];

    public function tecnologia(): BelongsTo
    {
        return $this->belongsTo(Tecnologia::class, 'tecnologia_id');
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(prestamos_archivos::class, 'prestamo_id');
    }
}
