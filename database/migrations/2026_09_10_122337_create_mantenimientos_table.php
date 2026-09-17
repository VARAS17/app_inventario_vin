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
        Schema::create('mantenimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tecnologia_id')->constrained('tecnologias')->onDelete('cascade');
            $table->enum('area_origen',['TI','Administracion','Imagen','Mesa de partes','Secretaría','Despacho Vicerrectoral']);
            $table->date('fecha_envio');
            $table->date('fecha_ingreso')->nullable();
            $table->string('motivo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mantenimientos');
    }
};
