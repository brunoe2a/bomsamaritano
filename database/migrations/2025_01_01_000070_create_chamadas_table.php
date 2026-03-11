<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chamadas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('turma_id')->constrained('turmas')->cascadeOnDelete();
            $table->date('data');
            $table->foreignId('professor_id')->nullable()->constrained('professores')->nullOnDelete();
            $table->text('observacoes')->nullable();
            $table->timestamps();

            $table->unique(['turma_id', 'data']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chamadas');
    }
};
