<?php

namespace App\Enums;

/**
 * Perfis de usuário com hierarquia de acesso
 */
enum PerfilUsuario: string
{
    case ADMINISTRADOR = 'administrador';
    case GESTOR = 'gestor';
    case OPERADOR = 'operador';
    case AUDITOR = 'auditor';

    public function label(): string
    {
        return match($this) {
            self::ADMINISTRADOR => 'Administrador',
            self::GESTOR => 'Gestor Municipal',
            self::OPERADOR => 'Operador',
            self::AUDITOR => 'Auditor',
        };
    }

    /**
     * Retorna permissões padrão do perfil
     */
    public function permissoes(): array
    {
        return match($this) {
            self::ADMINISTRADOR => [
                'usuarios.*',
                'municipios.*',
                'multas.*',
                'infracoes.*',
                'recursos.*',
                'auditoria.*',
                'configuracoes.*'
            ],
            self::GESTOR => [
                'multas.*',
                'recursos.*',
                'relatorios.*',
                'auditoria.visualizar',
                'dashboard.*'
            ],
            self::OPERADOR => [
                'multas.criar',
                'multas.visualizar',
                'multas.editar',
                'veiculos.*',
                'agentes.*',
                'dashboard.visualizar'
            ],
            self::AUDITOR => [
                'auditoria.*',
                'multas.visualizar',
                'recursos.visualizar',
                'relatorios.*'
            ],
        };
    }

    /**
     * Nível hierárquico (maior = mais poder)
     */
    public function nivel(): int
    {
        return match($this) {
            self::ADMINISTRADOR => 4,
            self::GESTOR => 3,
            self::AUDITOR => 2,
            self::OPERADOR => 1,
        };
    }
}
