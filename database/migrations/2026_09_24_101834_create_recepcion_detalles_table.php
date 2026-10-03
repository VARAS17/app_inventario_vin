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
        Schema::create('recepcion_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recepcion_id')
                  ->constrained('recepciones_abastecimiento')
                  ->onDelete('cascade');
            $table->foreignId('util_id')
                  ->constrained('utiles')
                  ->onDelete('cascade');
            $table->integer('cantidad_recibida');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recepcion_detalles');
    }
};
