<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saude_convocacao_aluno', function (Blueprint $table) {
            $table->id();
            $table->foreignId('convocacao_id')->constrained('saude_convocacoes')->cascadeOnDelete();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->boolean('presente')->default(false);
            $table->string('observacao')->nullable();
            $table->timestamps();

            $table->unique(['convocacao_id', 'aluno_id'], 'saude_conv_aluno_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saude_convocacao_aluno');
    }
};
