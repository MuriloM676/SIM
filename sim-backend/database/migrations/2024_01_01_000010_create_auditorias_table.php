<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tabela de auditoria IMUTÁVEL
     * Registra todas as ações críticas do sistema
     * Compliance LGPD
     */
    public function up(): void
    {
        Schema::create('auditorias', function (Blueprint $table) {
            $table->id();
            
            // Identificação do usuário
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios');
            $table->foreignId('municipio_id')->nullable()->constrained('municipios');
            
            // Ação realizada
            $table->string('tipo', 50)->comment('criacao, alteracao, exclusao, visualizacao, login, logout');
            $table->string('entidade', 100)->nullable()->comment('Nome da entidade/tabela afetada');
            $table->unsignedBigInteger('entidade_id')->nullable();
            $table->text('descricao');
            
            // Dados da operação
            $table->json('dados_antes')->nullable()->comment('Estado anterior (JSON)');
            $table->json('dados_depois')->nullable()->comment('Estado posterior (JSON)');
            
            // Contexto técnico
            $table->string('ip', 45);
            $table->text('user_agent')->nullable();
            
            // LGPD
            $table->string('base_legal_lgpd', 200)->nullable();
            
            // Timestamp
            $table->timestamp('data_hora')->useCurrent();
            
            // Índices
            $table->index('usuario_id');
            $table->index('municipio_id');
            $table->index('tipo');
            $table->index('entidade');
            $table->index(['entidade', 'entidade_id']);
            $table->index('data_hora');
            
            // Timestamps padrão
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditorias');
    }
};
