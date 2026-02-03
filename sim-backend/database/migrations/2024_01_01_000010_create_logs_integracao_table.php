<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logs_integracao', function (Blueprint $table) {
            $table->id();
            $table->string('entidade', 50);
            $table->unsignedBigInteger('entidade_id');
            $table->string('tipo', 50);
            $table->string('endpoint', 255)->nullable();
            $table->text('payload_envio')->nullable();
            $table->text('payload_resposta')->nullable();
            $table->integer('status_http')->nullable();
            $table->boolean('sucesso')->default(false);
            $table->integer('tentativa')->default(1);
            $table->timestamps();
            
            $table->index(['entidade', 'entidade_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logs_integracao');
    }
};
