<?php

namespace RoyScheepens\HexonExport\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyIpWhitelist
{

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $whitelist = config('hexon-export.ip_whitelist', []);

        abort_if(app()->environment('production')
            && count($whitelist) > 0
            && !in_array($request->ip(), $whitelist, true), 403, 'You are not allowed to access this resource.');

        return $next($request);
    }
}
