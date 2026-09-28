<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class entregas_personal extends Model
{
    use HasFactory;

    protected $table = 'entregas_personal';

    protected $fillable = [
        'personal_id',
        'fecha_entrega',
        'observaciones',
    ];

    protected $casts = [
        'fecha_entrega' => 'date',
    ];

    /**
     * Relación: Pertenece al trabajador (Personal) que recibió los útiles.
     */
    public function personal(): BelongsTo
    {
        return $this->belongsTo(Personal::class, 'personal_id');
    }

    /**
     * Relación: Lista de útiles entregados en este vale/acto.
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(entrega_detalle::class, 'entrega_id');
    }
}