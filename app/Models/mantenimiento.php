<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class mantenimiento extends Model
{
    use HasFactory;

    protected $table= 'mantenimientos';
    protected $fillable = [
        'tecnologia_id',
        'area_origen',
        'fecha_envio',
        'fecha_ingreso',
        'motivo',
    ];

    public function tecnologia(): BelongsTo
    {
        return $this->belongsTo(Tecnologia::class, 'tecnologia_id');
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(mantenimiento_archivos::class, 'mantenimiento_id');
    }
}
