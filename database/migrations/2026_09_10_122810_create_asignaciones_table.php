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
        Schema::create('asignaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tecnologia_id')->constrained('tecnologias')->onDelete('cascade');
            $table->foreignId('personal_id')->nullable()->constrained('personal')->onDelete('set null');
            $table->enum('area_origen',['VIN']);
            $table->enum('area_destino',['TI','Administracion','Imagen','Mesa de partes','Secretaría','Despacho Vicerrectoral']);
            $table->date('fecha_traspaso');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignaciones');
    }
};
