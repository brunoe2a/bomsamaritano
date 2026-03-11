<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('responsaveis', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('endereco_rua')->nullable();
            $table->string('endereco_numero')->nullable();
            $table->string('endereco_complemento')->nullable();
            $table->string('endereco_bairro')->nullable();
            $table->string('endereco_cidade')->nullable();
            $table->string('endereco_estado', 2)->nullable();
            $table->string('endereco_cep', 10)->nullable();
            $table->string('telefone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('cpf', 14)->unique()->nullable();
            $table->enum('renda_familiar', ['menos_1_salario', 'ate_2_salarios', 'acima_3_salarios'])->nullable();
            $table->boolean('veiculo_proprio')->default(false);
            $table->boolean('casa_propria')->default(false);
            $table->boolean('cadastro_cras')->default(false);
            $table->boolean('auxilio_governo')->default(false);
            $table->boolean('desempregado')->default(false);
            $table->boolean('autorizacao_sozinho')->default(false);
            $table->boolean('autorizacao_imagem')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('responsaveis');
    }
};
