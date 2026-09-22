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
        Schema::create('prestamos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tecnologia_id')->constrained('tecnologias')->onDelete('cascade');
            $table->foreignId('area_origen_id')->constrained('areas')->onDelete('cascade'); // <-- Enlazado a áreas
            $table->string('area_destino');      // Destino externo o evento
            $table->string('responsable')->nullable(); // Persona externa
            $table->date('fecha_prestamo');
            $table->date('fecha_devolucion_pactada');
            $table->date('fecha_devolucion_real')->nullable();
            $table->string('estado_previo')->nullable(); 
            $table->text('observacion_devolucion')->nullable(); // Opcional para la trazabilidad del retorno
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestamos');
    }
};
