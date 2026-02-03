<?php

namespace App\Policies;

use App\Models\Multa;
use App\Models\Usuario;

class MultaPolicy
{
    /**
     * Determina se o usuário pode visualizar qualquer multa
     */
    public function viewAny(Usuario $usuario): bool
    {
        return $usuario->temPermissao('multas.visualizar');
    }

    /**
     * Determina se o usuário pode visualizar a multa
     */
    public function view(Usuario $usuario, Multa $multa): bool
    {
        // Administradores podem ver tudo
        if ($usuario->isAdministrador()) {
            return true;
        }

        // Usuários só podem ver multas do seu município
        return $usuario->municipio_id === $multa->municipio_id
            && $usuario->temPermissao('multas.visualizar');
    }

    /**
     * Determina se o usuário pode criar multas
     */
    public function create(Usuario $usuario): bool
    {
        return $usuario->temPermissao('multas.criar');
    }

    /**
     * Determina se o usuário pode atualizar a multa
     */
    public function update(Usuario $usuario, Multa $multa): bool
    {
        return $usuario->municipio_id === $multa->municipio_id
            && $usuario->temPermissao('multas.editar')
            && $multa->podeSerEditada();
    }

    /**
     * Determina se o usuário pode excluir a multa
     */
    public function delete(Usuario $usuario, Multa $multa): bool
    {
        // Multas não podem ser deletadas, apenas canceladas
        return false;
    }
}
