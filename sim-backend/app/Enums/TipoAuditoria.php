<?php

namespace App\Enums;

/**
 * Tipos de ações auditáveis no sistema
 */
enum TipoAuditoria: string
{
    case CRIACAO = 'criacao';
    case ATUALIZACAO = 'atualizacao';
    case EXCLUSAO = 'exclusao';
    case VISUALIZACAO = 'visualizacao';
    case LOGIN = 'login';
    case LOGOUT = 'logout';
    case ACESSO_NEGADO = 'acesso_negado';
    case TRANSICAO_STATUS = 'transicao_status';
    case UPLOAD_ARQUIVO = 'upload_arquivo';
    case DOWNLOAD_ARQUIVO = 'download_arquivo';
    case INTEGRACAO_ENVIADA = 'integracao_enviada';
    case INTEGRACAO_RECEBIDA = 'integracao_recebida';
    case RECURSO_PROTOCOLADO = 'recurso_protocolado';
    case RECURSO_JULGADO = 'recurso_julgado';

    public function label(): string
    {
        return match($this) {
            self::CRIACAO => 'Criação',
            self::ATUALIZACAO => 'Atualização',
            self::EXCLUSAO => 'Exclusão',
            self::VISUALIZACAO => 'Visualização',
            self::LOGIN => 'Login',
            self::LOGOUT => 'Logout',
            self::ACESSO_NEGADO => 'Acesso Negado',
            self::TRANSICAO_STATUS => 'Transição de Status',
            self::UPLOAD_ARQUIVO => 'Upload de Arquivo',
            self::DOWNLOAD_ARQUIVO => 'Download de Arquivo',
            self::INTEGRACAO_ENVIADA => 'Integração Enviada',
            self::INTEGRACAO_RECEBIDA => 'Integração Recebida',
            self::RECURSO_PROTOCOLADO => 'Recurso Protocolado',
            self::RECURSO_JULGADO => 'Recurso Julgado',
        };
    }

    /**
     * Define se a ação é considerada crítica para LGPD
     */
    public function isCritica(): bool
    {
        return in_array($this, [
            self::VISUALIZACAO,
            self::DOWNLOAD_ARQUIVO,
            self::EXCLUSAO,
            self::ACESSO_NEGADO,
        ]);
    }
}
