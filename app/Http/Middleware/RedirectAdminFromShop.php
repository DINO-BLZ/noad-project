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
                || in_array($name, ['login', 'logout', 'register', 'password.request', 'password.email', 'password.reset', 'password.update'])
            );

            if (! $allowed) {
                return redirect()->route('admin.dashboard');
            }
        }

        return $next($request);
    }
}
