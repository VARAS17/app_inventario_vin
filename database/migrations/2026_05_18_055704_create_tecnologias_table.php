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
            $table->string('nombre'); // Ej: Monitor, Laptop
            $table->string('marca');  // Solicitado: marca
            $table->string('serie')->nullable();
            $table->enum('lugar',['Oficina principal','Sala de Reuniones','Oficina de comunicaciones', 'Almacen', 'Cocina'])->default('Oficina Principal');
            // Estado solicitado: en funcionamiento, guardado, malogrado
            $table->enum('estado', ['En funcionamiento', 'Guardado', 'Malogrado'])->default('En funcionamiento');
            
            // Asignación: a quién está designado (Ej: monitor de Jose)
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
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
