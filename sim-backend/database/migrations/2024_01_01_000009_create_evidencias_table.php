<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evidencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('multa_id')->constrained('multas');
            $table->foreignId('usuario_upload_id')->constrained('usuarios');
            
            $table->string('tipo', 30)->comment('foto, documento, laudo, outro');
            $table->string('nome_original', 255);
            $table->string('nome_armazenado', 255);
            $table->string('mime_type', 100);
            $table->integer('tamanho_bytes');
            $table->string('hash_sha256', 64)->comment('Hash do arquivo para integridade');
            $table->text('descricao')->nullable();
            $table->string('caminho_storage', 500);
            
            $table->timestamp('data_upload')->useCurrent();
            $table->timestamps();
            
            $table->index('multa_id');
            $table->index('tipo');
            $table->index('hash_sha256');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidencias');
    }
};
