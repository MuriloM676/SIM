<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('municipios', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_ibge', 7)->unique()->comment('Código IBGE do município');
            $table->string('nome', 100);
            $table->string('uf', 2);
            $table->string('cnpj', 18)->unique();
            $table->boolean('ativo')->default(true);
            $table->json('configuracoes')->nullable()->comment('Configurações específicas do município');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['uf', 'nome']);
            $table->index('ativo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('municipios');
    }
};
