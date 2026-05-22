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
        Schema::create('utiles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Ej: Hojas Bond, Lapiceros
            $table->integer('cantidad'); // Solicitado: cantidad
            // Unidad solicitada: cajas o unidad
            $table->enum('unidad', ['Cajas', 'Paquetes', 'Unidad'])->default('Unidad');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utiles');
    }
};
