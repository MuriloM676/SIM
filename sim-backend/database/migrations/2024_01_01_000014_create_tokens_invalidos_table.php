<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tokens_invalidos', function (Blueprint $table) {
            $table->id();
            $table->text('token');
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->onDelete('cascade');
            $table->string('tipo', 20)->default('logout')->comment('logout, expiracao, revogacao');
            $table->timestamp('data_invalidacao')->useCurrent();
            $table->timestamp('expira_em');
            
            $table->index('usuario_id');
            $table->index('data_invalidacao');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tokens_invalidos');
    }
};
