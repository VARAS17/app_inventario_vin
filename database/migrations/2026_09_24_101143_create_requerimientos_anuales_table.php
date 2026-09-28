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
        Schema::create('requerimientos_anuales', function (Blueprint $table) {
            $table->id();
            $table->integer('anio')->unique(); // Ej: 2024, 2025
            $table->string('codigo')->nullable(); // Ej: OFICIO-N-012-2024
            $table->string('documento_path')->nullable(); // Ruta del PDF en storage/app local
            $table->string('estado')->default('Abierto'); // 'Abierto' o 'Cerrado'
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requerimientos_anuales');
    }
};
