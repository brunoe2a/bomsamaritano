<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expediente_escalados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expediente_id')->constrained('expedientes')->cascadeOnDelete();
            $table->morphs('escalavel');
            $table->boolean('presente')->default(true);
            $table->string('justificativa')->nullable();
            $table->timestamps();

            $table->unique(['expediente_id', 'escalavel_type', 'escalavel_id'], 'expediente_escalavel_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expediente_escalados');
    }
};
