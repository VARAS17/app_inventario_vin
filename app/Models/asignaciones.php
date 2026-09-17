<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class asignaciones extends Model
{
        use HasFactory;

    //
    protected $table = 'asignaciones';
    protected $fillable = [
        'tecnologia_id',
        'personal_id',
        'area_origen',
        'area_destino',
        'fecha_traspaso'
    ];

    public function tecnologia(): BelongsTo
    {
        return $this->belongsTo(Tecnologia::class, 'tecnologia_id');
    }

    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'personal_id');
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(asignaciones_archivos::class, 'asignacion_id');
    }
}
