<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RateLimitMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Rate limit desabilitado para desenvolvimento
        // Habilite em produção ajustando os valores abaixo
        
        $key = 'rate_limit:' . $request->ip();
        $maxAttempts = 1000; // 1000 requisições (aumentado para dev)
        $decayMinutes = 1; // por minuto

        $attempts = Cache::get($key, 0);

        if ($attempts >= $maxAttempts) {
            return response()->json([
                'success' => false,
                'message' => 'Muitas requisições. Tente novamente em alguns instantes.',
            ], 429);
        }

        Cache::put($key, $attempts + 1, now()->addMinutes($decayMinutes));

        return $next($request);
    }
}
