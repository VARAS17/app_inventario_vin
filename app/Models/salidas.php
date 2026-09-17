<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class salidas extends Model
{
    use HasFactory;

    //
    protected $table = 'salidas';
    protected $fillable = [
        'tecnologia_id',
        'motivo',
        'area_destino',
        'responsable',
        'fecha_salida'

    ];

    public function tecnologia(): BelongsTo
    {
        return $this->belongsTo(tecnologia::class, 'tecnologia_id');
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(salidas_archivos::class, 'salida_id');
    }
}
