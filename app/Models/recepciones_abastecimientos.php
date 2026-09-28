<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class recepciones_abastecimientos extends Model
{
    use HasFactory;

    protected $table = 'recepciones_abastecimiento';

    protected $fillable = [
        'requerimiento_anual_id',
        'numero_documento',
        'documento_path',
        'fecha_recepcion',
        'observaciones',
    ];

    protected $casts = [
        'fecha_recepcion' => 'date',
    ];

    /**
     * Relación: Pertenece al requerimiento anual de ese año.
     */
    public function requerimientoAnual(): BelongsTo
    {
        return $this->belongsTo(requerimientos_anuales::class, 'requerimiento_anual_id');
    }

    /**
     * Relación: Contiene los útiles específicos que llegaron en este lote.
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(recepcion_detalles::class, 'recepcion_id');
    }
}