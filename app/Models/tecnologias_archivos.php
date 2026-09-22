<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class tecnologias_archivos extends Model
{
    use HasFactory;
    //
    protected $table = 'tecnologias_archivos';

    protected $fillable = [
        'nombre_archivo',
        'ruta_archivo',
        'tecnologia_id'
    ];

        public function tecnologia(): BelongsTo
    {
        return $this->belongsTo(Tecnologia::class, 'tecnologia_id');
    }
}
