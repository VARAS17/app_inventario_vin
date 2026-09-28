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
        Schema::create('entrega_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entrega_id')
                  ->constrained('entregas_personal')
                  ->onDelete('cascade');
            $table->foreignId('util_id')
                  ->constrained('utiles')
                  ->onDelete('cascade');
            $table->integer('cantidad_entregada');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entrega_detalles');
    }
};
