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
        Schema::create('mobiliarios_asignaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mobiliario_id')->constrained('mobiliarios')->onDelete('cascade');
            
            // Custodio (NULLABLE para permitir muebles de espacios comunes como salas de espera o pasillos)
            $table->foreignId('personal_id')->nullable()->constrained('personal')->onDelete('set null');
            
            // Trazabilidad de Áreas
            $table->foreignId('area_origen_id')->nullable()->constrained('areas')->onDelete('set null');
            $table->foreignId('area_destino_id')->constrained('areas')->onDelete('cascade');
            
            $table->date('fecha_traspaso');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobiliarios_asignaciones');
    }
};
