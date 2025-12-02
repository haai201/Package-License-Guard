<?php

namespace Susoft\LicenseGuard\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Susoft\LicenseGuard\Services\LicenseClient;

class LicenseGuardMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldBypass($request)) {
            return $next($request);
        }

        if (! LicenseClient::check($request)) {
            return response()
                ->view('license_guard::blocked', [], 403);
        }

        return $next($request);
    }

    protected function shouldBypass(Request $request): bool
    {
        $path = $request->path();

        if ($request->is('_health') || $request->is('_ping')) {
            return true;
        }

        return false;
    }
}
