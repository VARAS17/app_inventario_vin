<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mobiliarios', function (Blueprint $table) {
            $table->id();
            
            // Identificación
            $table->string('codigo_vin')->unique(); // UNT-VINMOB-0001
            $table->string('codigo_inventario_unt')->nullable(); // Placa patrimonial opcional
            $table->string('nombre'); // Ej: Silla ejecutiva ergonómica, Mesa de reuniones
            
            // Detalles del bien
            $table->string('proveedor')->nullable();
            $table->text('descripcion')->nullable(); // Material, color, medidas, notas
            $table->string('foto')->nullable(); // Foto principal (OPCIONAL)
            
            // Estado y Fecha
            $table->enum('estado', ['Disponible', 'Asignado', 'De baja'])->default('Disponible');
            $table->date('fecha_ingreso');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobiliarios');
    }
};