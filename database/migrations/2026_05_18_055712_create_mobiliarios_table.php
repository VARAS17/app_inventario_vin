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
            $table->string('nombre'); 
            $table->string('material'); 
            $table->string('color');    
            
            // Campo de imagen añadido
            $table->string('imagen')->nullable(); // Guarda la ruta del archivo
            
            $table->enum('estado', ['Bueno', 'Regular', 'A la basura'])->default('Bueno');
            $table->enum('lugar',['Oficina principal','Sala de Reuniones','Oficina de comunicaciones', 'Almacen', 'Cocina'])->default('Oficina Principal');
            
            $table->foreignId('personal_id')->nullable()->constrained('personal')->onDelete('set null');
            
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