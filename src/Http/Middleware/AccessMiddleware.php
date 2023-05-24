<?php

namespace Qz\Admin\Permission\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Qz\Admin\Permission\Cores\Subsystem\SubsystemIdGet;
use Qz\Admin\Permission\Facades\Access;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystem;
use Qz\Admin\Permission\Models\CustomerSubsystem;

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
        $subsystemId = SubsystemIdGet::init()
            ->run()
            ->getId();
        if (empty($subsystemId)) {
            throw new Exception('子系统不存在');
        }
        Access::setSubsystemId($subsystemId);
        $adminUserId = Auth::guard('admin')->id();
        if (empty($adminUserId)) {
            throw new Exception('用户未登陆');
        }
        $model = CustomerSubsystem::query()
            ->select('customer_id', 'id')
            ->where('subsystem_id', $subsystemId)
            ->whereHas('adminUserCustomerSubsystems', function (Builder $builder) use ($adminUserId) {
                $builder->where('admin_user_id', $adminUserId)
                    ->where('status', AdminUserCustomerSubsystem::STATUS_NORMAL)
                    ->whereHas('adminUser');
            })
            ->orderByDesc('id')
            ->first();
        Access::setCustomerSubsystemId(Arr::get($model, 'id'));
        Access::setCustomerId(Arr::get($model, 'customer_id'));
        $AdminUserCustomerSubsystem = AdminUserCustomerSubsystem::query()
            ->where('admin_user_id', $adminUserId)
            ->where('customer_subsystem_id', Arr::get($model, 'id'))
            ->where('administrator', true)
            ->first();
        if (!empty($AdminUserCustomerSubsystem)) {
            Access::setAdministrator(true);
        }
        return $next($request);
    }
}
