<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Tecnologia extends Model
{
        use HasFactory;
    /**
     * Campos permitidos para asignación masiva.
     */
    // Dentro de app/Models/Tecnologia.php
    protected $table = 'tecnologias';
    protected $fillable = [
        'codigo_vin',
        'nombre',
        'marca',
        'serie',
        'estado',
        'fecha_ingreso',
        'proveedor',
        'foto',
    ];

    public static function generarSiguienteCodigoVin(): string
    {
        // Obtiene el último registro insertado
        $ultimo = self::orderBy('id', 'desc')->first();

        if (!$ultimo || !$ultimo->codigo_vin) {
            return 'UNT-VIN-0001';
        }

        // Extrae los últimos 4 dígitos numéricos y le suma 1
        $partes = explode('-', $ultimo->codigo_vin);
        $ultimoNumero = intval(end($partes));
        $siguienteNumero = str_pad($ultimoNumero + 1, 4, '0', STR_PAD_LEFT);

        return 'UNT-VIN-' . $siguienteNumero;
    }

    // Archivos propios del equipo
    public function archivos(): HasMany
    {
        return $this->hasMany(tecnologias_archivos::class, 'tecnologia_id');
    }

    // Historial de Asignaciones
    public function asignaciones(): HasMany
    {
        return $this->hasMany(Asignaciones::class, 'tecnologia_id');
    }

    // Última asignación activa (para saber rápido quién lo tiene)
    public function ultimaAsignacion(): HasOne
    {
        return $this->hasOne(Asignaciones::class, 'tecnologia_id')->latestOfMany();
    }

    // Historial de Préstamos
    public function prestamos(): HasMany
    {
        return $this->hasMany(Prestamos::class, 'tecnologia_id');
    }

    // Historial de Mantenimientos
    public function mantenimientos(): HasMany
    {
        return $this->hasMany(mantenimiento::class, 'tecnologia_id');
    }

    // Registro de Salida (si ya fue dado de baja)
    public function salida(): HasOne
    {
        return $this->hasOne(salidas::class, 'tecnologia_id');
    }

}