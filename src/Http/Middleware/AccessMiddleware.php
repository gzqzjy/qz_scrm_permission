<?php

namespace Qz\Admin\Permission\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Qz\Admin\Permission\Cores\Subsystem\SubsystemIdGet;
use Qz\Admin\Permission\Facades\Access;
use Qz\Admin\Permission\Models\AdminPage;
use Qz\Admin\Permission\Models\AdminUser;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystem;
use Qz\Admin\Permission\Models\CustomerSubsystem;

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
        $model = CustomerSubsystem::query()
            ->select('customer_id', 'id')
            ->where('subsystem_id', $subsystemId)
            ->whereHas('adminUserCustomerSubsystems', function (Builder $builder) use ($adminUserId) {
                $builder->where('admin_user_id', $adminUserId)
                    ->where('status', AdminUserCustomerSubsystem::STATUS_NORMAL)
                    ->whereHas('adminUser', function (Builder $builder) {
                        $builder->where('status', AdminUser::STATUS_NORMAL);
                    });
            })
            ->orderByDesc('id')
            ->first();
        if (!empty($model)) {
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
        }
        return $next($request);
    }
}
