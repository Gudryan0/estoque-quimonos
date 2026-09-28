<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UsuarioAtivoMiddleware
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        if (
            Auth::check()
            && !Auth::user()->ativo
        ) {
            Auth::logout();

            $request
                ->session()
                ->invalidate();

            $request
                ->session()
                ->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' =>
                        'Sua conta está inativa. Procure um administrador.',
                ]);
        }

        return $next($request);
    }
}
