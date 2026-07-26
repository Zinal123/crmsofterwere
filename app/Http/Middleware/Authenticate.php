<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if ($request->expectsJson()) {
            return null;
        }

        // The client portal lives under /portal on its own "client" guard -
        // an unauthenticated visit there must land on the client login page,
        // never the staff one (and vice versa).
        if ($request->is('portal*')) {
            return route('client.login');
        }

        return route('login');
    }
}
