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
        Schema::create('entregas_personal', function (Blueprint $table) {
            $table->id();
            // Vinculado a tu tabla 'personal' existente
            $table->foreignId('personal_id')
                  ->constrained('personal')
                  ->onDelete('cascade');
            $table->date('fecha_entrega');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entregas_personals');
    }
};
