<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('multas', function (Blueprint $table) {
            $table->id();
            $table->string('auto_infracao', 20)->unique()->comment('Número único do auto de infração');
            $table->foreignId('municipio_id')->constrained('municipios');
            $table->foreignId('infracao_id')->constrained('infracoes');
            $table->foreignId('veiculo_id')->constrained('veiculos');
            $table->foreignId('agente_id')->constrained('agentes');
            $table->foreignId('usuario_criador_id')->constrained('usuarios');
            
            $table->string('placa', 7);
            $table->date('data_infracao');
            $table->time('hora_infracao');
            $table->string('local_infracao', 255);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            
            $table->decimal('velocidade_medida', 6, 2)->nullable();
            $table->decimal('velocidade_maxima', 6, 2)->nullable();
            
            $table->text('observacoes')->nullable();
            
            $table->string('status', 30)->default('rascunho')->index();
            $table->decimal('valor_multa', 10, 2);
            $table->integer('pontos_cnh');
            
            $table->string('numero_detran', 30)->nullable()->comment('Número após envio ao Detran');
            $table->timestamp('data_envio_detran')->nullable();
            $table->timestamp('data_notificacao')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['municipio_id', 'status']);
            $table->index('placa');
            $table->index('data_infracao');
            $table->index('auto_infracao');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('multas');
    }
};
