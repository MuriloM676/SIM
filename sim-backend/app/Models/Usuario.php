<?php

namespace App\Models;

use App\Enums\PerfilUsuario;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'usuarios';

    protected $fillable = [
        'municipio_id',
        'nome',
        'email',
        'cpf',
        'password',
        'perfil',
        'matricula',
        'telefone',
        'ativo',
        'ultimo_acesso',
        'ultimo_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'perfil' => PerfilUsuario::class,
        'ativo' => 'boolean',
        'ultimo_acesso' => 'datetime',
        'email_verified_at' => 'datetime',
    ];

    /**
     * Relacionamento com município
     */
    public function municipio(): BelongsTo
    {
        return $this->belongsTo(Municipio::class);
    }

    /**
     * Verifica se o usuário tem uma permissão
     */
    public function temPermissao(string $permissao): bool
    {
        $permissoesUsuario = $this->perfil->permissoes();

        foreach ($permissoesUsuario as $padraoPermissao) {
            // Suporta wildcard: usuarios.*
            $regex = '/^' . str_replace('*', '.*', $padraoPermissao) . '$/';
            if (preg_match($regex, $permissao)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifica se é administrador
     */
    public function isAdministrador(): bool
    {
        return $this->perfil === PerfilUsuario::ADMINISTRADOR;
    }

    /**
     * Verifica se é gestor
     */
    public function isGestor(): bool
    {
        return $this->perfil === PerfilUsuario::GESTOR;
    }

    /**
     * Scope para usuários ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }

    /**
     * Scope para filtrar por município
     */
    public function scopePorMunicipio($query, int $municipioId)
    {
        return $query->where('municipio_id', $municipioId);
    }

    /**
     * Registra último acesso
     */
    public function registrarAcesso(string $ip): void
    {
        $this->update([
            'ultimo_acesso' => now(),
            'ultimo_ip' => $ip,
        ]);
    }
}
