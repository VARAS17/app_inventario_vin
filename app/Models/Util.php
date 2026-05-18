<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Util extends Model
{
    /**
     * Nombre de la tabla (Correcto: Laravel por defecto buscaría 'utils')
     */
    protected $table = 'utiles';

    /**
     * Campos permitidos para asignación masiva.
     */
    protected $fillable = [
        'nombre',
        'cantidad',
        'unidad',
    ];
}