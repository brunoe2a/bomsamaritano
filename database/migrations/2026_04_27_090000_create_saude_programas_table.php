<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saude_programas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->enum('area', ['odontologia', 'psicologia', 'medica', 'nutricao', 'fonoaudiologia', 'geral'])->default('geral');
            $table->text('descricao')->nullable();
            $table->string('cor', 7)->nullable();
            $table->enum('status', ['ativo', 'inativo'])->default('ativo');
            $table->timestamps();

            $table->index(['area', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saude_programas');
    }
};
