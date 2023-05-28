<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }

    public function handle($request, Closure $next, ...$guards)
    {
        if ($access_token = $request -> cookie('access_token')) {
            $request -> headers -> set('Authorization', 'Bearer ' . $access_token);
        }
        
        $this -> authenticate($request, $guards);
        return $next($request);
    }
}
