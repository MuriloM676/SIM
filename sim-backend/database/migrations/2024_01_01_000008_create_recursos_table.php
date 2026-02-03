<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recursos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_protocolo', 30)->unique();
            $table->foreignId('multa_id')->constrained('multas');
            $table->foreignId('usuario_criador_id')->constrained('usuarios');
            
            $table->string('tipo', 30)->comment('defesa_previa, recurso_primeira_instancia, recurso_segunda_instancia');
            $table->text('fundamentacao');
            
            $table->string('status', 30)->default('em_analise');
            $table->text('parecer_tecnico')->nullable();
            $table->string('decisao', 30)->nullable()->comment('deferido, parcialmente_deferido, indeferido');
            $table->text('justificativa_decisao')->nullable();
            
            $table->foreignId('usuario_julgador_id')->nullable()->constrained('usuarios');
            $table->timestamp('data_julgamento')->nullable();
            $table->timestamp('data_protocolo')->useCurrent();
            
            $table->timestamps();
            
            $table->index('multa_id');
            $table->index('status');
            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recursos');
    }
};
