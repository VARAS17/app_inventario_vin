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
        Schema::create('mantenimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tecnologia_id')->constrained('tecnologias')->onDelete('cascade');
            $table->foreignId('area_origen_id')->constrained('areas')->onDelete('cascade'); // Origen estandarizado
            
            // Salida a mantenimiento
            $table->enum('tipo', ['Preventivo', 'Correctivo'])->default('Correctivo');
            $table->string('taller_proveedor')->nullable(); // Quién lo repara
            $table->string('motivo');                      // Falla reportada
            $table->date('fecha_envio');
            $table->string('estado_previo')->nullable();   // Disponible o Asignado

            // Retorno de mantenimiento
            $table->date('fecha_ingreso')->nullable();     // Fecha real de retorno
            $table->text('solucion')->nullable();          // Qué trabajo se realizó
            $table->boolean('quedo_operativo')->default(true); // true = Vuelve a circular | false = Irreparable (De baja)
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mantenimientos');
    }
};
