<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turmas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->foreignId('curso_id')->constrained('cursos')->cascadeOnDelete();
            $table->time('horario_inicio')->nullable();
            $table->time('horario_fim')->nullable();
            $table->json('dias_semana')->nullable();
            $table->enum('periodo', ['segunda_sexta', 'sabados'])->default('segunda_sexta');
            $table->integer('capacidade_maxima')->default(30);
            $table->year('ano_letivo');
            $table->enum('status', ['planejada', 'em_andamento', 'encerrada'])->default('planejada');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turmas');
    }
};
