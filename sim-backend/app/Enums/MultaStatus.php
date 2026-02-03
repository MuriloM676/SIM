<?php

namespace App\Enums;

/**
 * Enum para controle do workflow de status da multa
 * Estados baseados em fluxo governamental real
 */
enum MultaStatus: string
{
    case RASCUNHO = 'rascunho';
    case REGISTRADA = 'registrada';
    case ENVIADA_ORGAO_EXTERNO = 'enviada_orgao_externo';
    case NOTIFICADA = 'notificada';
    case EM_RECURSO = 'em_recurso';
    case RECURSO_DEFERIDO = 'recurso_deferido';
    case RECURSO_INDEFERIDO = 'recurso_indeferido';
    case ENCERRADA = 'encerrada';
    case CANCELADA = 'cancelada';

    /**
     * Retorna o label humanizado do status
     */
    public function label(): string
    {
        return match($this) {
            self::RASCUNHO => 'Rascunho',
            self::REGISTRADA => 'Registrada',
            self::ENVIADA_ORGAO_EXTERNO => 'Enviada ao Órgão Externo',
            self::NOTIFICADA => 'Notificada',
            self::EM_RECURSO => 'Em Recurso',
            self::RECURSO_DEFERIDO => 'Recurso Deferido',
            self::RECURSO_INDEFERIDO => 'Recurso Indeferido',
            self::ENCERRADA => 'Encerrada',
            self::CANCELADA => 'Cancelada',
        };
    }

    /**
     * Define transições válidas entre estados
     */
    public function transicoesPossiveis(): array
    {
        return match($this) {
            self::RASCUNHO => [self::REGISTRADA, self::CANCELADA],
            self::REGISTRADA => [self::ENVIADA_ORGAO_EXTERNO, self::CANCELADA],
            self::ENVIADA_ORGAO_EXTERNO => [self::NOTIFICADA, self::CANCELADA],
            self::NOTIFICADA => [self::EM_RECURSO, self::ENCERRADA],
            self::EM_RECURSO => [self::RECURSO_DEFERIDO, self::RECURSO_INDEFERIDO],
            self::RECURSO_DEFERIDO => [self::ENCERRADA],
            self::RECURSO_INDEFERIDO => [self::ENCERRADA],
            self::ENCERRADA => [],
            self::CANCELADA => [],
        };
    }

    /**
     * Verifica se pode transitar para outro status
     */
    public function podeTransitarPara(MultaStatus $novoStatus): bool
    {
        return in_array($novoStatus, $this->transicoesPossiveis());
    }

    /**
     * Retorna cor para UI
     */
    public function cor(): string
    {
        return match($this) {
            self::RASCUNHO => 'gray',
            self::REGISTRADA => 'blue',
            self::ENVIADA_ORGAO_EXTERNO => 'indigo',
            self::NOTIFICADA => 'purple',
            self::EM_RECURSO => 'yellow',
            self::RECURSO_DEFERIDO => 'green',
            self::RECURSO_INDEFERIDO => 'red',
            self::ENCERRADA => 'slate',
            self::CANCELADA => 'red',
        };
    }
}
