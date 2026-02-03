<?php

namespace App\DTOs\Usuario;

use App\Enums\PerfilUsuario;

/**
 * DTO para criação de usuário
 */
class CreateUsuarioDTO
{
    public function __construct(
        public readonly string $nome,
        public readonly string $email,
        public readonly string $cpf,
        public readonly string $password,
        public readonly PerfilUsuario $perfil,
        public readonly int $municipio_id,
        public readonly bool $ativo = true,
        public readonly ?string $matricula = null,
        public readonly ?string $telefone = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            nome: $data['nome'],
            email: $data['email'],
            cpf: preg_replace('/\D/', '', $data['cpf']),
            password: $data['password'],
            perfil: PerfilUsuario::from($data['perfil']),
            municipio_id: $data['municipio_id'],
            ativo: $data['ativo'] ?? true,
            matricula: $data['matricula'] ?? null,
            telefone: $data['telefone'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'nome' => $this->nome,
            'email' => $this->email,
            'cpf' => $this->cpf,
            'password' => bcrypt($this->password),
            'perfil' => $this->perfil->value,
            'municipio_id' => $this->municipio_id,
            'ativo' => $this->ativo,
            'matricula' => $this->matricula,
            'telefone' => $this->telefone,
        ];
    }
}
