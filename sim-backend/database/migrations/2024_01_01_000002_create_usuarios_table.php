<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('municipio_id')->constrained('municipios');
            $table->string('nome', 150);
            $table->string('email')->unique();
            $table->string('cpf', 11)->unique();
            $table->string('password');
            $table->string('perfil', 20)->comment('administrador, gestor, operador, auditor');
            $table->string('matricula', 20)->nullable();
            $table->string('telefone', 20)->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamp('ultimo_acesso')->nullable();
            $table->string('ultimo_ip', 45)->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index('municipio_id');
            $table->index('perfil');
            $table->index('ativo');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
