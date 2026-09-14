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
        Schema::create('tecnologias', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_vin')->unique();
            $table->string('nombre'); // Ej: Monitor, Laptop
            $table->string('marca');  // Solicitado: marca
            $table->string('serie')->nullable();
            // Estado solicitado: en funcionamiento, guardado, malogrado
            $table->enum('estado', ['Disponible', 'Asignado', 'En Mantenimiento'])
                ->default('Disponible');
            $table->date('fecha_ingreso');
            $table->string('proveedor');
            $table->string('foto');            
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tecnologias');
    }
};
