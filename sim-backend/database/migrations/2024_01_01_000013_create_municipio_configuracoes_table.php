<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('municipio_configuracoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipio_id')->unique()->constrained('municipios');
            
            // Configurações LGPD
            $table->integer('dias_retencao_dados')->default(1825)->comment('5 anos padrão');
            $table->boolean('lgpd_ativo')->default(true);
            $table->text('finalidade_tratamento_dados')->nullable();
            $table->text('base_legal_lgpd')->nullable();
            
            // Configurações de sistema
            $table->boolean('permitir_edicao_multa_registrada')->default(false);
            $table->integer('prazo_defesa_previa_dias')->default(15);
            $table->integer('prazo_recurso_jari_dias')->default(30);
            $table->integer('prazo_recurso_cetran_dias')->default(30);
            
            // Integração Detran
            $table->boolean('integracao_detran_ativa')->default(true);
            $table->string('detran_endpoint', 255)->nullable();
            $table->string('detran_token', 255)->nullable();
            
            // Notificações
            $table->boolean('notificar_email')->default(true);
            $table->boolean('notificar_sms')->default(false);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('municipio_configuracoes');
    }
};
