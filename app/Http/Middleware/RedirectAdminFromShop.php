<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectAdminFromShop
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->is_admin) {
            $name = $request->route()?->getName();

            $allowed = $name !== null && (
                str_starts_with($name, 'admin.')
                || in_array($name, ['login', 'logout', 'password.request', 'password.email', 'password.reset', 'password.update'], true)
            );

            if (! $allowed) {
                if ($request->expectsJson()) {
                    abort(403, 'Les comptes administrateurs ne peuvent pas effectuer d\'actions réservées aux clients.');
                }

                return redirect()->route('admin.dashboard');
            }
        }

        return $next($request);
    }
}
