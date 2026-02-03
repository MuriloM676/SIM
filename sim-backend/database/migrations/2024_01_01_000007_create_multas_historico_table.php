<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('multas_historico', function (Blueprint $table) {
            $table->id();
            $table->foreignId('multa_id')->constrained('multas');
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios');
            
            $table->string('status_anterior', 30);
            $table->string('status_novo', 30);
            $table->text('justificativa')->nullable();
            $table->string('ip', 45)->nullable();
            
            $table->timestamp('data_transicao')->useCurrent();
            
            $table->index('multa_id');
            $table->index('data_transicao');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('multas_historico');
    }
};
