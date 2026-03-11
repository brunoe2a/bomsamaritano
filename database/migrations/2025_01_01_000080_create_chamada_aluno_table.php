<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chamada_aluno', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chamada_id')->constrained('chamadas')->cascadeOnDelete();
            $table->foreignId('aluno_id')->constrained('alunos')->cascadeOnDelete();
            $table->boolean('presente')->default(false);
            $table->string('observacao')->nullable();
            $table->timestamps();

            $table->unique(['chamada_id', 'aluno_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chamada_aluno');
    }
};
