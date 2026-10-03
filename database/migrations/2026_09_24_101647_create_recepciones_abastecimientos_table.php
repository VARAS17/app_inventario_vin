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
        Schema::create('recepciones_abastecimiento', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requerimiento_anual_id')
                  ->constrained('requerimientos_anuales')
                  ->onDelete('cascade');
            $table->string('numero_documento')->nullable(); // PECOSA, Guía de Remisión, etc.
            $table->string('documento_path')->nullable(); // Acta de entrega/PDF escaneado
            $table->date('fecha_recepcion');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recepciones_abastecimientos');
    }
};
