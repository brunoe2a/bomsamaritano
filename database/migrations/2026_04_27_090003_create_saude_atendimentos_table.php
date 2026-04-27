<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saude_atendimentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->foreignId('programa_id')->constrained('saude_programas')->cascadeOnDelete();
            $table->date('data_atendimento');
            $table->string('profissional')->nullable();
            $table->text('observacoes')->nullable();
            $table->foreignId('convocacao_id')->nullable()->constrained('saude_convocacoes')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['aluno_id', 'data_atendimento']);
            $table->index(['programa_id', 'data_atendimento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saude_atendimentos');
    }
};
