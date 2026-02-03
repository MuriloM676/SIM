<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('multa_id')->constrained('multas');
            $table->string('tipo', 50);
            $table->string('destinatario_nome', 100);
            $table->text('destinatario_endereco')->nullable();
            $table->string('destinatario_email', 100)->nullable();
            $table->string('arquivo_pdf', 255)->nullable();
            $table->timestamp('data_envio')->nullable();
            $table->timestamp('data_recebimento')->nullable();
            $table->date('prazo_defesa')->nullable();
            $table->string('codigo_rastreio', 50)->nullable();
            $table->timestamps();
            
            $table->index('multa_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificacoes');
    }
};
