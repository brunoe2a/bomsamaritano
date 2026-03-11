<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financeiro_categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->enum('tipo', ['receita', 'despesa']);
            $table->string('descricao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financeiro_categorias');
    }
};
