<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('relatorios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->foreignId('municipio_id')->constrained('municipios');
            
            $table->string('tipo', 50)->comment('multas, arrecadacao, recursos, auditoria');
            $table->string('formato', 10)->comment('pdf, csv, xlsx');
            $table->string('status', 20)->default('processando');
            
            $table->json('filtros')->nullable();
            $table->string('arquivo_path', 500)->nullable();
            $table->integer('tamanho_bytes')->nullable();
            
            $table->timestamp('data_solicitacao')->useCurrent();
            $table->timestamp('data_conclusao')->nullable();
            $table->timestamp('expira_em')->nullable();
            
            $table->timestamps();
            
            $table->index(['usuario_id', 'data_solicitacao']);
            $table->index('status');
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relatorios');
    }
};
