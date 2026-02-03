<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integracoes_log', function (Blueprint $table) {
            $table->id();
            
            $table->string('sistema_externo', 50)->index()->comment('detran, renavam, etc');
            $table->string('operacao', 100)->comment('consulta_veiculo, envio_multa, etc');
            $table->string('tipo', 20)->comment('request, response');
            
            $table->foreignId('multa_id')->nullable()->constrained('multas');
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios');
            
            $table->string('metodo_http', 10);
            $table->string('endpoint', 500);
            $table->integer('status_http')->nullable();
            
            $table->json('request_headers')->nullable();
            $table->json('request_body')->nullable();
            $table->json('response_headers')->nullable();
            $table->json('response_body')->nullable();
            
            $table->integer('tempo_resposta_ms')->nullable();
            $table->boolean('sucesso')->index();
            $table->text('erro')->nullable();
            
            $table->timestamp('data_hora')->useCurrent()->index();
            
            $table->index(['sistema_externo', 'sucesso']);
            $table->index(['multa_id', 'data_hora']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integracoes_log');
    }
};
