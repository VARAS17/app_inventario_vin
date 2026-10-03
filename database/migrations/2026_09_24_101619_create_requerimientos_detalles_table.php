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
        Schema::create('requerimiento_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requerimiento_anual_id')
                  ->constrained('requerimientos_anuales')
                  ->onDelete('cascade');
            $table->foreignId('util_id')
                  ->constrained('utiles')
                  ->onDelete('cascade');
            $table->integer('cantidad_solicitada'); // Meta anual pedida
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requerimientos_detalles');
    }
};
