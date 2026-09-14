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
        Schema::create('tecnologias_archivos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_archivo');
            $table->string('ruta_archivo');

            //clave foranea
            $table->foreignId('tecnologia_id')->constrained('tecnologias')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tecnologias_archivos');
    }
};
