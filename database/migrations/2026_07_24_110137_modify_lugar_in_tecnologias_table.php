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
            // En SQLite, 'change()' recrea la tabla internamente para aplicar los cambios
            $table->enum('lugar', [
                'Oficina principal',
                'Sala de Reuniones',
                'Oficina de comunicaciones', 
                'Almacen', 
                'Cocina', 
                'Sala Reuniones/ropero 1', 
                'Sala Reuniones/Ropero 2'
            ])->default('Oficina principal')->change();
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
