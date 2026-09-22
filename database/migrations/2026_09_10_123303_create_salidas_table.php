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
        Schema::create('salidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tecnologia_id')->constrained('tecnologias')->onDelete('cascade');
            $table->foreignId('area_origen_id')->constrained('areas')->onDelete('cascade'); // Origen autocompletado
            
            // Clasificación y sustento (como string flexible)
            $table->string('tipo_baja');                 // Ej: "Obsolescencia", "Chatarreo RAEE", "Donación", etc.            
            // Disposición final
            $table->string('destino_final');             // A dónde va el bien
            $table->string('responsable_recepcion')->nullable();     // Quién firma la recepción
            $table->date('fecha_salida');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salidas');
    }
};
