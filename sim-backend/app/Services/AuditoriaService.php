<?php

namespace App\Services;

use App\Enums\TipoAuditoria;
use App\Models\Auditoria;
use Illuminate\Support\Facades\Auth;

/**
 * Serviço centralizado de auditoria
 * Garante logs completos e compliance LGPD
 */
class AuditoriaService
{
    /**
     * Registra uma ação no sistema
     */
    public function registrar(
        TipoAuditoria $tipoAcao,
        string $entidade,
        ?int $entidadeId = null,
        string $descricao = '',
        ?array $dadosAntes = null,
        ?array $dadosDepois = null,
        ?int $usuarioId = null
    ): Auditoria {
        $usuario = $usuarioId ? \App\Models\Usuario::find($usuarioId) : Auth::user();

        $dados = [
            'usuario_id' => $usuario?->id,
            'usuario_nome' => $usuario?->nome,
            'usuario_email' => $usuario?->email,
            'usuario_perfil' => $usuario?->perfil?->value,
            'tipo_acao' => $tipoAcao->value,
            'entidade' => $entidade,
            'entidade_id' => $entidadeId,
            'descricao' => $descricao,
            'dados_antes' => $dadosAntes ? $this->sanitizarDadosSensiveis($dadosAntes) : null,
            'dados_depois' => $dadosDepois ? $this->sanitizarDadosSensiveis($dadosDepois) : null,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metodo_http' => request()->method(),
            'url' => request()->fullUrl(),
            'acao_critica' => $tipoAcao->isCritica(),
            'data_hora' => now(),
        ];

        return Auditoria::create($dados);
    }

    /**
     * Registra acesso a dados pessoais (LGPD)
     */
    public function registrarAcessoDadosPessoais(
        string $entidade,
        int $entidadeId,
        string $descricao
    ): Auditoria {
        return $this->registrar(
            tipoAcao: TipoAuditoria::VISUALIZACAO,
            entidade: $entidade,
            entidadeId: $entidadeId,
            descricao: $descricao
        );
    }

    /**
     * Registra login
     */
    public function registrarLogin(int $usuarioId, bool $sucesso = true): Auditoria
    {
        return $this->registrar(
            tipoAcao: $sucesso ? TipoAuditoria::LOGIN : TipoAuditoria::ACESSO_NEGADO,
            entidade: 'usuarios',
            entidadeId: $usuarioId,
            descricao: $sucesso ? 'Login realizado' : 'Tentativa de login falhou',
            usuarioId: $usuarioId
        );
    }

    /**
     * Registra logout
     */
    public function registrarLogout(int $usuarioId): Auditoria
    {
        return $this->registrar(
            tipoAcao: TipoAuditoria::LOGOUT,
            entidade: 'usuarios',
            entidadeId: $usuarioId,
            descricao: 'Logout realizado',
            usuarioId: $usuarioId
        );
    }

    /**
     * Sanitiza dados sensíveis antes de logar
     * Remove senhas, tokens, etc.
     */
    private function sanitizarDadosSensiveis(array $dados): array
    {
        $camposSensiveis = [
            'password',
            'password_confirmation',
            'token',
            'api_token',
            'remember_token',
        ];

        foreach ($camposSensiveis as $campo) {
            if (isset($dados[$campo])) {
                $dados[$campo] = '[REDACTED]';
            }
        }

        return $dados;
    }

    /**
     * Busca logs de auditoria com filtros
     */
    public function buscarLogs(array $filtros = [], int $perPage = 50)
    {
        $query = Auditoria::query()
            ->with('usuario')
            ->orderBy('data_hora', 'desc');

        if (isset($filtros['usuario_id'])) {
            $query->where('usuario_id', $filtros['usuario_id']);
        }

        if (isset($filtros['entidade'])) {
            $query->where('entidade', $filtros['entidade']);
        }

        if (isset($filtros['entidade_id'])) {
            $query->where('entidade_id', $filtros['entidade_id']);
        }

        if (isset($filtros['tipo_acao'])) {
            $query->where('tipo_acao', $filtros['tipo_acao']);
        }

        if (isset($filtros['data_inicio'])) {
            $query->where('data_hora', '>=', $filtros['data_inicio']);
        }

        if (isset($filtros['data_fim'])) {
            $query->where('data_hora', '<=', $filtros['data_fim']);
        }

        if (isset($filtros['criticas']) && $filtros['criticas']) {
            $query->criticas();
        }

        return $query->paginate($perPage);
    }
}
