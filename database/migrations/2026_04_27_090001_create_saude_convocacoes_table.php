<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saude_convocacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('programa_id')->constrained('saude_programas')->cascadeOnDelete();
            $table->string('titulo');
            $table->date('data');
            $table->time('hora')->nullable();
            $table->string('local')->nullable();
            $table->string('profissional')->nullable();
            $table->text('observacoes')->nullable();
            $table->enum('status', ['planejada', 'realizada', 'cancelada'])->default('planejada');
            $table->foreignId('unidade_id')->nullable()->constrained('unidades')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['data', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saude_convocacoes');
    }
};
