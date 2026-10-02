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
        Schema::create('mobiliarios_salidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mobiliario_id')->constrained('mobiliarios')->onDelete('cascade');
            $table->foreignId('area_origen_id')->constrained('areas')->onDelete('cascade'); // Área donde estaba antes de salir
            
            $table->string('tipo_baja'); // Ej: Deterioro irreparable, Donación, Pérdida, Obsolescencia
            $table->string('destino_final'); // A dónde va el bien (Ej: Almacén central de bajas, Reciclaje)
            $table->string('responsable_recepcion')->nullable(); // Persona o entidad receptora
            $table->date('fecha_salida');
            
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobiliarios_salidas');
    }
};
