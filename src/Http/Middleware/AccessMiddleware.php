<?php

namespace Qz\Admin\Permission\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Qz\Admin\Permission\Facades\Access;

class AccessMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws Exception
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('admin')->user();
        if (empty($user)) {
            return $next($request);
        }
        Access::setAdminUserId(Arr::get($user, 'id'));
        Access::setCustomerId(Arr::get($user, 'customer_id'));
        Access::setAdministrator((boolean) Arr::get($user, 'administrator'));
        return $next($request);
    }
}
