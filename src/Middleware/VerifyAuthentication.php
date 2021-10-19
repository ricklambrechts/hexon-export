<?php

namespace RoyScheepens\HexonExport\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyAuthentication
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
        $authEnabled = config('hexon-export.authentication.enabled', false);
        if (!$authEnabled) {
            return $next($request);
        }

        abort_if(!$this->authenticationIsValid($request), 403, 'You are not allowed to access this resource.');

        return $next($request);
    }

    public function authenticationIsValid(Request $request): bool
    {
        $authUsername = config('hexon-export.authentication.username', '');
        if (empty($authUsername)) {
            return false;
        }

        $authPassword = config('hexon-export.authentication.password', '');
        if (empty($authPassword)) {
            return false;
        }

        return $request->getUser() === $authUsername && $request->getPassword() === $authPassword;
    }
}
