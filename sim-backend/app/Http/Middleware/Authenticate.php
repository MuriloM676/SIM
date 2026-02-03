<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Authenticate
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token não fornecido',
            ], 401);
        }

        // Decodificar token base64: id:email:timestamp
        $decoded = base64_decode($token);
        $parts = explode(':', $decoded);

        if (count($parts) !== 3) {
            return response()->json([
                'success' => false,
                'message' => 'Token inválido',
            ], 401);
        }

        [$userId, $email, $timestamp] = $parts;

        // Verificar se token expirou (24 horas)
        if (time() - $timestamp > 86400) {
            return response()->json([
                'success' => false,
                'message' => 'Token expirado',
            ], 401);
        }

        // Carregar usuário
        $usuario = DB::table('usuarios')
            ->join('municipios', 'usuarios.municipio_id', '=', 'municipios.id')
            ->select(
                'usuarios.*',
                'municipios.nome as municipio_nome'
            )
            ->where('usuarios.id', $userId)
            ->where('usuarios.email', $email)
            ->where('usuarios.ativo', true)
            ->first();

        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado ou inativo',
            ], 401);
        }

        // Adicionar dados do usuário ao request
        $request->merge([
            'user_id' => $usuario->id,
            'user_email' => $usuario->email,
            'user_nome' => $usuario->nome,
            'user_perfil' => $usuario->perfil,
            'user_municipio_id' => $usuario->municipio_id,
            'user_municipio_nome' => $usuario->municipio_nome,
        ]);

        return $next($request);
    }
}
