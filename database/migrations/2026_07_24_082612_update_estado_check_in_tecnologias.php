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
        Schema::table('tecnologias', function (Blueprint $table) {
            // Esto intentará recrear la columna con los nuevos valores
            $table->enum('estado', ['En funcionamiento', 'Guardado', 'En Mantenimiento', 'Reparacion', 'Dar de Baja'])
                ->default('En funcionamiento')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tecnologias', function (Blueprint $table) {
            //
        });
    }
};
