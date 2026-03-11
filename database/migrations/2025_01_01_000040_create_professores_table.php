<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('professores', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('foto')->nullable();
            $table->string('cpf', 14)->unique()->nullable();
            $table->string('rg')->nullable();
            $table->date('data_nascimento')->nullable();
            $table->string('telefone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('endereco_rua')->nullable();
            $table->string('endereco_numero')->nullable();
            $table->string('endereco_complemento')->nullable();
            $table->string('endereco_bairro')->nullable();
            $table->string('endereco_cidade')->nullable();
            $table->string('endereco_estado', 2)->nullable();
            $table->string('endereco_cep', 10)->nullable();
            $table->json('especialidade')->nullable();
            $table->enum('tipo_vinculo', ['voluntario', 'contratado'])->default('voluntario');
            $table->date('data_inicio')->nullable();
            $table->enum('status', ['ativo', 'inativo'])->default('ativo');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professores');
    }
};
