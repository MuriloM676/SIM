<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipio_id')->constrained('municipios');
            $table->string('matricula', 20)->index();
            $table->string('nome', 150);
            $table->string('cpf', 11)->unique();
            $table->string('cargo', 100);
            $table->string('lotacao', 100)->nullable()->comment('Setor/Departamento');
            $table->boolean('ativo')->default(true);
            $table->date('data_admissao')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['matricula', 'municipio_id']);
            $table->index('ativo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agentes');
    }
};
