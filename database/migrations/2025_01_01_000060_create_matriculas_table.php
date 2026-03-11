<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matriculas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->foreignId('turma_id')->constrained('turmas')->cascadeOnDelete();
            $table->enum('tipo', ['nova', 'rematricula'])->default('nova');
            $table->year('ano_letivo');
            $table->enum('status', ['ativa', 'cancelada', 'trancada', 'concluida'])->default('ativa');
            $table->date('data_matricula');
            $table->timestamps();

            $table->unique(['aluno_id', 'turma_id', 'ano_letivo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matriculas');
    }
};
