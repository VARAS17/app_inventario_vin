<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimientos_utiles', function (Blueprint $table) {
            $table->id();
            
            // Relación con la tabla utiles
            $table->foreignId('util_id')
                  ->constrained('utiles')
                  ->onDelete('cascade');

            // Tipo de movimiento: ingreso o egreso
            $table->enum('tipo', ['Ingreso', 'Egreso']);
            
            // Cantidad que entra o sale
            $table->integer('cantidad');
            
            // Descripción del por qué del movimiento
            $table->string('descripcion')->nullable();
            
            // Fecha del movimiento (opcional, ya que timestamps trae created_at)
            $table->timestamp('fecha')->useCurrent();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_utiles');
    }
};