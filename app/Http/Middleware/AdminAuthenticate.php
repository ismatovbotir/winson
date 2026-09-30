<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;

/**
 * `auth` for /admin, but sends guests to admin.login — there is no public
 * `login` route on this site.
 */
class AdminAuthenticate extends Authenticate
{
    protected function redirectTo(Request $request): ?string
    {
        return route('admin.login');
    }
}
