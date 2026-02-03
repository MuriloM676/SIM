<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('veiculos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipio_id')->constrained('municipios');
            $table->string('placa', 7);
            $table->string('renavam', 11)->nullable();
            $table->string('chassi', 17)->nullable();
            $table->string('marca', 50)->nullable();
            $table->string('modelo', 100)->nullable();
            $table->string('cor', 30)->nullable();
            $table->integer('ano_fabricacao')->nullable();
            $table->integer('ano_modelo')->nullable();
            $table->string('categoria', 30)->nullable()->comment('particular, comercial, oficial');
            $table->string('proprietario_nome', 150)->nullable();
            $table->string('proprietario_cpf_cnpj', 18)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['placa', 'municipio_id']);
            $table->index('renavam');
            $table->index('proprietario_cpf_cnpj');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('veiculos');
    }
};
