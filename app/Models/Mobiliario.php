<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Mobiliario extends Model
{
    use HasFactory;

    protected $table = 'mobiliarios';

    protected $fillable = [
        'codigo_vin',
        'codigo_inventario_unt',
        'nombre',
        'proveedor',
        'descripcion',
        'foto',
        'estado',
        'fecha_ingreso',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
    ];

    /**
     * Generador correlativo automático: UNT-VINMOB-0001
     */
    public static function generarSiguienteCodigoVin(): string
    {
        $ultimo = self::orderBy('id', 'desc')->first();

        if (!$ultimo || !$ultimo->codigo_vin) {
            return 'UNT-VINMOB-0001';
        }

        $partes = explode('-', $ultimo->codigo_vin);
        $ultimoNumero = intval(end($partes));
        $siguienteNumero = str_pad($ultimoNumero + 1, 4, '0', STR_PAD_LEFT);

        return 'UNT-VINMOB-' . $siguienteNumero;
    }

    // Archivos / Evidencias de Alta (facturas, fotos iniciales)
    public function archivos(): HasMany
    {
        return $this->hasMany(MobiliarioArchivo::class, 'mobiliario_id');
    }

    // Historial completo de Transferencias
    public function asignaciones(): HasMany
    {
        return $this->hasMany(MobiliarioAsignacion::class, 'mobiliario_id');
    }

    // Última transferencia registrada (quién o qué área lo tiene actualmente)
    public function ultimaAsignacion(): HasOne
    {
        return $this->hasOne(MobiliarioAsignacion::class, 'mobiliario_id')->latestOfMany();
    }

    // Registro de baja definitiva (si ya salió del inventario)
    public function salida(): HasOne
    {
        return $this->hasOne(MobiliarioSalida::class, 'mobiliario_id');
    }

    public function transferencias(): HasMany
    {
        // Reemplaza 'TransferenciaMobi::class' por el nombre real de tu modelo de transferencias de mobiliario
        return $this->hasMany(MobiliarioAsignacion::class, 'mobiliario_id');
    }
}