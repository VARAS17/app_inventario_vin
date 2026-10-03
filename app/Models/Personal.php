<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;


class Personal extends Model
{
    // Definimos la tabla explícitamente si es necesario
    protected $table = 'personal';

    protected $fillable = [
        'nombre',
        'apellido',
        'cargo',
        'area_id',
        'foto_perfil',
        'grado_academico',
        'correo',
    ];


    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    // Relación con las asignaciones que ha recibido este personal
    public function asignaciones(): HasMany
    {
        return $this->hasMany(Asignaciones::class, 'personal_id');
    }

    /**
     * Relación: Un personal puede tener muchos muebles asignados.
     */
    public function mobiliarios(): HasMany
    {
        return $this->hasMany(Mobiliario::class);
    }

    /**
     * Obtener iniciales basadas en Nombre y Apellido
     */
    public function initials(): string
    {
        $inicialNombre = Str::substr($this->nombre, 0, 1);
        $inicialApellido = $this->apellido ? Str::substr($this->apellido, 0, 1) : '';
        
        return strtoupper($inicialNombre . $inicialApellido);
    }
}