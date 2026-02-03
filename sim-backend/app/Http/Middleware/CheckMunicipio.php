<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckMunicipio
{
    public function handle(Request $request, Closure $next)
    {
        // Admin tem acesso a tudo
        if ($request->user_perfil === 'administrador') {
            return $next($request);
        }

        // Demais perfis: garantir que municipio_id seja do usuário
        if ($request->has('municipio_id')) {
            if ($request->municipio_id != $request->user_municipio_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso negado. Você não tem permissão para acessar dados de outro município.',
                ], 403);
            }
        }

        return $next($request);
    }
}
