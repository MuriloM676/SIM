<?php

namespace App\Models;

use App\Enums\TipoAuditoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model de Auditoria - IMUTÁVEL
 * Não pode ser atualizado ou deletado após criação
 */
class Auditoria extends Model
{
    protected $table = 'auditorias';

    // Desabilita timestamps padrão
    const UPDATED_AT = null;
    const CREATED_AT = 'data_hora';

    protected $fillable = [
        'usuario_id',
        'usuario_nome',
        'usuario_email',
        'usuario_perfil',
        'tipo_acao',
        'entidade',
        'entidade_id',
        'descricao',
        'dados_antes',
        'dados_depois',
        'ip',
        'user_agent',
        'metodo_http',
        'url',
        'acao_critica',
        'data_hora',
    ];

    protected $casts = [
        'tipo_acao' => TipoAuditoria::class,
        'dados_antes' => 'array',
        'dados_depois' => 'array',
        'acao_critica' => 'boolean',
        'data_hora' => 'datetime',
    ];

    /**
     * Desabilita update e delete
     */
    public function update(array $attributes = [], array $options = [])
    {
        throw new \Exception('Registros de auditoria não podem ser atualizados');
    }

    public function delete()
    {
        throw new \Exception('Registros de auditoria não podem ser deletados');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    /**
     * Cria registro de auditoria
     */
    public static function registrar(array $dados): self
    {
        return self::create($dados);
    }

    /**
     * Scope para ações críticas (LGPD)
     */
    public function scopeCriticas($query)
    {
        return $query->where('acao_critica', true);
    }

    /**
     * Scope por entidade
     */
    public function scopePorEntidade($query, string $entidade, ?int $entidadeId = null)
    {
        $query->where('entidade', $entidade);

        if ($entidadeId) {
            $query->where('entidade_id', $entidadeId);
        }

        return $query;
    }

    /**
     * Scope por período
     */
    public function scopePorPeriodo($query, string $dataInicio, string $dataFim)
    {
        return $query->whereBetween('data_hora', [$dataInicio, $dataFim]);
    }
}
