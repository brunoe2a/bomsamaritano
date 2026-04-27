<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_instances', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('instance_name')->unique();
            $table->string('numero')->nullable();
            $table->enum('status', ['disconnected', 'connecting', 'connected'])->default('disconnected');
            $table->longText('qr_code')->nullable();
            $table->timestamp('last_status_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_instances');
    }
};
