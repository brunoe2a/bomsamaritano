<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doadores', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->enum('tipo', ['pessoa_fisica', 'pessoa_juridica'])->default('pessoa_fisica');
            $table->string('cpf_cnpj')->nullable();
            $table->string('telefone')->nullable();
            $table->string('email')->nullable();
            $table->string('endereco')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doadores');
    }
};
