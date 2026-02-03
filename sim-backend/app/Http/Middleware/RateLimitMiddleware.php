<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RateLimitMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $key = 'rate_limit:' . $request->ip();
        $maxAttempts = 60; // 60 requisições
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
