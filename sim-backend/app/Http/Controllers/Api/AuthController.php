<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $usuario = DB::table('usuarios')
            ->where('email', $request->email)
            ->where('ativo', true)
            ->first();

        if (!$usuario || !Hash::check($request->password, $usuario->password)) {
            throw ValidationException::withMessages([
                'email' => ['As credenciais fornecidas estão incorretas.'],
            ]);
        }

        // Buscar município
        $municipio = DB::table('municipios')
            ->where('id', $usuario->municipio_id)
            ->first();

        // Criar token (simulado - em produção usar Sanctum)
        $token = base64_encode($usuario->id . ':' . $usuario->email . ':' . time());

        // Registrar log de acesso
        DB::table('log_acessos')->insert([
            'usuario_id' => $usuario->id,
            'municipio_id' => $usuario->municipio_id,
            'acao' => 'login',
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'data_hora' => now(),
        ]);

        // Atualizar último acesso
        DB::table('usuarios')
            ->where('id', $usuario->id)
            ->update(['ultimo_acesso' => now()]);

        // Mapear perfil
        $perfilLabels = [
            'administrador' => 'Administrador',
            'gestor' => 'Gestor',
            'operador' => 'Operador',
            'auditor' => 'Auditor',
        ];

        return response()->json([
            'success' => true,
            'message' => 'Login realizado com sucesso',
            'data' => [
                'user' => [
                    'id' => $usuario->id,
                    'nome' => $usuario->nome,
                    'email' => $usuario->email,
                    'perfil' => $usuario->perfil,
                    'perfil_label' => $perfilLabels[$usuario->perfil] ?? $usuario->perfil,
                    'municipio_id' => $usuario->municipio_id,
                    'municipio' => $municipio->nome ?? '',
                ],
                'token' => $token,
            ],
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $token = $request->bearerToken();
        $usuarioId = $this->getUserIdFromToken($token);

        if ($usuarioId) {
            // Registrar logout
            DB::table('log_acessos')->insert([
                'usuario_id' => $usuarioId,
                'municipio_id' => null,
                'acao' => 'logout',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'data_hora' => now(),
            ]);

            // Invalidar token
            DB::table('tokens_invalidos')->insert([
                'token' => $token,
                'usuario_id' => $usuarioId,
                'tipo' => 'logout',
                'data_invalidacao' => now(),
                'expira_em' => now()->addDays(7),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout realizado com sucesso',
        ]);
    }

    /**
     * Informações do usuário autenticado
     */
    public function me(Request $request)
    {
        $token = $request->bearerToken();
        $usuarioId = $this->getUserIdFromToken($token);

        if (!$usuarioId) {
            return response()->json([
                'success' => false,
                'message' => 'Token inválido',
            ], 401);
        }

        $usuario = DB::table('usuarios')
            ->where('id', $usuarioId)
            ->where('ativo', true)
            ->first();

        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado',
            ], 404);
        }

        $municipio = DB::table('municipios')
            ->where('id', $usuario->municipio_id)
            ->first();

        $perfilLabels = [
            'administrador' => 'Administrador',
            'gestor' => 'Gestor',
            'operador' => 'Operador',
            'auditor' => 'Auditor',
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $usuario->id,
                'nome' => $usuario->nome,
                'email' => $usuario->email,
                'perfil' => $usuario->perfil,
                'perfil_label' => $perfilLabels[$usuario->perfil] ?? $usuario->perfil,
                'municipio_id' => $usuario->municipio_id,
                'municipio' => $municipio->nome ?? '',
            ],
        ]);
    }

    /**
     * Extrai ID do usuário do token
     */
    private function getUserIdFromToken($token)
    {
        if (!$token) {
            return null;
        }

        try {
            $decoded = base64_decode($token);
            $parts = explode(':', $decoded);
            return isset($parts[0]) ? (int) $parts[0] : null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
