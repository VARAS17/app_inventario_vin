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
        Schema::create('debajas', function (Blueprint $table) {
            $table->id();
            
            // Información básica común
            $table->string('nombre');
            $table->string('tipo_inventario'); // Ejemplo: 'Mobiliario', 'Tecnologia'
            $table->string('motivo')->default('Malogrado'); // Malogrado, Obsoleto, Perdido
            $table->date('fecha_baja');

            // Relación con el personal (quién lo tenía al momento de la baja)
            // Usamos nullable() por si el objeto no estaba asignado a nadie
            $table->foreignId('personal_id')->nullable()->constrained('personal')->onDelete('set null');

            // LA CLAVE: El campo JSON para guardar campos distintos (color, marca, serie, etc.)
            $table->json('detalles')->nullable();

            // Para guardar la ruta de la imagen
            $table->string('imagen')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
            Schema::dropIfExists('debajas');
    }
};
