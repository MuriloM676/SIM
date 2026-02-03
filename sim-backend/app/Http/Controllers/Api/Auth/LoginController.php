<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditoriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __construct(
        private AuditoriaService $auditoriaService
    ) {}

    /**
     * Login do usuário
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $usuario = \App\Models\Usuario::where('email', $request->email)->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            if ($usuario) {
                $this->auditoriaService->registrarLogin($usuario->id, false);
            }

            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas estão incorretas.'],
            ]);
        }

        if (!$usuario->ativo) {
            throw ValidationException::withMessages([
                'email' => ['Usuário inativo. Contate o administrador.'],
            ]);
        }

        // Cria token de acesso
        $token = $usuario->createToken('auth-token')->plainTextToken;

        // Registra acesso
        $usuario->registrarAcesso($request->ip());

        // Auditoria
        $this->auditoriaService->registrarLogin($usuario->id, true);

        return response()->json([
            'success' => true,
            'message' => 'Login realizado com sucesso',
            'data' => [
                'user' => [
                    'id' => $usuario->id,
                    'nome' => $usuario->nome,
                    'email' => $usuario->email,
                    'perfil' => $usuario->perfil->value,
                    'perfil_label' => $usuario->perfil->label(),
                    'municipio_id' => $usuario->municipio_id,
                    'municipio' => $usuario->municipio->nome,
                ],
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    /**
     * Logout do usuário
     */
    public function logout(Request $request): JsonResponse
    {
        $usuario = $request->user();

        // Auditoria
        $this->auditoriaService->registrarLogout($usuario->id);

        // Revoga todos os tokens do usuário
        $usuario->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout realizado com sucesso',
        ]);
    }

    /**
     * Retorna informações do usuário autenticado
     */
    public function me(Request $request): JsonResponse
    {
        $usuario = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $usuario->id,
                'nome' => $usuario->nome,
                'email' => $usuario->email,
                'cpf' => $usuario->cpf,
                'perfil' => $usuario->perfil->value,
                'perfil_label' => $usuario->perfil->label(),
                'matricula' => $usuario->matricula,
                'telefone' => $usuario->telefone,
                'municipio_id' => $usuario->municipio_id,
                'municipio' => $usuario->municipio,
                'permissoes' => $usuario->perfil->permissoes(),
                'ultimo_acesso' => $usuario->ultimo_acesso,
            ],
        ]);
    }
}
