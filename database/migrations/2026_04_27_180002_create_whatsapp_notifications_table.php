<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('whatsapp_instance_id')->constrained('whatsapp_instances')->cascadeOnDelete();
            $table->foreignId('whatsapp_template_id')->nullable()->constrained('whatsapp_templates')->nullOnDelete();
            $table->foreignId('aluno_id')->nullable()->constrained('alunos')->nullOnDelete();
            $table->foreignId('responsavel_id')->nullable()->constrained('responsaveis')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('numero');
            $table->string('numero_normalizado')->nullable();
            $table->text('mensagem');
            $table->enum('status', ['pendente', 'validando', 'numero_invalido', 'enviando', 'enviado', 'falhou'])->default('pendente');
            $table->boolean('numero_valido')->nullable();
            $table->text('erro')->nullable();
            $table->string('evolution_message_id')->nullable();
            $table->timestamp('enviado_em')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('numero_normalizado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_notifications');
    }
};
