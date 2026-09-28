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
        Schema::create('movimientos_utiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('util_id')
                  ->constrained('utiles')
                  ->onDelete('cascade');
            $table->string('tipo'); // 'Ingreso', 'Egreso', 'Saldo Inicial'
            $table->integer('cantidad');
            $table->integer('stock_anterior');
            $table->integer('stock_nuevo');
            $table->string('referencia_tipo')->nullable(); // 'Recepcion', 'Entrega', 'Ajuste'
            $table->unsignedBigInteger('referencia_id')->nullable(); // ID de la recepción o entrega
            $table->string('descripcion')->nullable(); // Ej: "Entrega a Juan Pérez" o "Llegada PECOSA 123"
            $table->date('fecha');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_utiles');
    }
};
