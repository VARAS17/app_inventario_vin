<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasMany;
// Eliminamos la importación de Fortify que fallaba

class User extends Authenticatable
{
    use HasFactory, Notifiable; // Quitamos TwoFactorAuthenticatable de aquí

    /**
     * Campos permitidos para asignación masiva.
     */
    protected $fillable = [
        'nombre',
        'apellido',
        'cargo',
        'email',
        'password',
    ];

    /**
     * Campos ocultos para la serialización (JSON/Arrays).
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * Relación: Un usuario puede tener muchos equipos tecnológicos asignados.
     */
    public function tecnologias(): HasMany
    {
        return $this->hasMany(Tecnologia::class);
    }

    /**
     * Relación: Un usuario puede tener muchos muebles asignados.
     */
    public function mobiliarios(): HasMany
    {
        return $this->hasMany(Mobiliario::class);
    }

    /**
     * Atributos que deben ser convertidos (Casts).
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Obtener iniciales basadas en Nombre y Apellido
     */
    public function initials(): string
    {
        // Añadimos una comprobación por si apellido está vacío
        $inicialNombre = Str::substr($this->nombre, 0, 1);
        $inicialApellido = $this->apellido ? Str::substr($this->apellido, 0, 1) : '';
        
        return strtoupper($inicialNombre . $inicialApellido);
    }
}