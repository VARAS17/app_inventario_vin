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
            $table->string('nombre'); // Ej: Hojas Bond A4, Bolígrafo Azul
            $table->string('marca')->nullable();
            $table->string('unidad')->default('Unidad'); // 'Unidad', 'Cajas', 'Paquetes'
            $table->integer('stock_actual')->default(0); // Cantidad física real en armario
            $table->integer('stock_minimo')->default(5); // Para alerta visual de agotamiento
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
