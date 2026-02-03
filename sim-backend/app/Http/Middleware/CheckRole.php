<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!$request->has('user_perfil')) {
            return response()->json([
                'success' => false,
                'message' => 'Não autenticado',
            ], 401);
        }

        if (!in_array($request->user_perfil, $roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Acesso negado. Perfil insuficiente.',
            ], 403);
        }

        return $next($request);
    }
}
