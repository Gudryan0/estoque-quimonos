<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdministradorMiddleware
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        if (
            !$request->user()
            || $request->user()->nivel_acesso !== 'administrador'
        ) {
            return redirect()
                ->route('dashboard')
                ->with(
                    'erro',
                    'Acesso restrito a administradores.'
                );
        }

        return $next($request);
    }
}
