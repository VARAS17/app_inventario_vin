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
            $table->string('nombre'); // Ej: Escritorio, Archivero
            $table->string('material'); // Solicitado: material
            $table->string('color');    // Solicitado: color
            // Estado solicitado: bueno, regular, a la basura
            $table->enum('estado', ['Bueno', 'Regular', 'A la basura'])->default('Bueno');
             $table->enum('lugar',['Oficina principal','Sala de Reuniones','Oficina de comunicaciones', 'Almacen', 'Cocina'])->default('Oficina Principal');
            // Asignación opcional por si el mueble es de alguien específico
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
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
