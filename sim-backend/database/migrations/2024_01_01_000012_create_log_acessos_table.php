<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_acessos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->onDelete('set null');
            $table->foreignId('municipio_id')->nullable()->constrained('municipios');
            
            $table->string('acao', 50)->comment('login, logout, visualizacao, criacao, edicao, exclusao');
            $table->string('entidade', 100)->nullable();
            $table->unsignedBigInteger('entidade_id')->nullable();
            $table->text('descricao')->nullable();
            
            $table->string('ip', 45);
            $table->text('user_agent')->nullable();
            $table->string('metodo_http', 10)->nullable();
            $table->string('url', 500)->nullable();
            
            $table->timestamp('data_hora')->useCurrent();
            
            $table->index(['usuario_id', 'data_hora']);
            $table->index(['acao', 'data_hora']);
            $table->index(['municipio_id', 'data_hora']);
            $table->index('entidade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_acessos');
    }
};
