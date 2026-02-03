<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('infracoes', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_ctb', 10)->unique()->comment('Código do artigo CTB');
            $table->text('descricao');
            $table->string('gravidade', 20)->comment('leve, media, grave, gravissima');
            $table->integer('pontos')->comment('Pontos na CNH');
            $table->decimal('valor', 10, 2)->comment('Valor da multa em reais');
            $table->text('detalhamento')->nullable();
            $table->boolean('medidor_velocidade')->default(false);
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index('codigo_ctb');
            $table->index('gravidade');
            $table->index('ativo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('infracoes');
    }
};
