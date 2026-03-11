<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financeiro_lancamentos', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['entrada', 'saida']);
            $table->foreignId('categoria_id')->constrained('financeiro_categorias')->cascadeOnDelete();
            $table->foreignId('doador_id')->nullable()->constrained('doadores')->nullOnDelete();
            $table->string('descricao');
            $table->decimal('valor', 10, 2);
            $table->date('data');
            $table->string('comprovante')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financeiro_lancamentos');
    }
};
