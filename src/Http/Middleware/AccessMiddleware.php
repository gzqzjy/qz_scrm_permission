<?php

namespace Qz\Admin\Access\Http\Middleware;

use App\Cores\Subsystem\SubsystemIdGet;
use App\Facades\Access;
use App\Models\AdminUserCustomerSubsystem;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class AccessMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $subsystemId = SubsystemIdGet::init()
            ->run()
            ->getId();
        Access::setSubsystemId($subsystemId);
        $adminUserId = Auth::guard('admin')->id();
        $model = AdminUserCustomerSubsystem::query()
            ->select('customer_id')
            ->where('subsystem_id', $subsystemId)
            ->where('admin_user_id', $adminUserId)
            ->orderByDesc('id')
            ->first();
        if (!empty($model)) {
            Access::setCustomerId(Arr::get($model, 'customer_id'));
        }
        return $next($request);
    }
}
