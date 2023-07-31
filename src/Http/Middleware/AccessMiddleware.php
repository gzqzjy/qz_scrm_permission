<?php

namespace Qz\Admin\Permission\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Qz\Admin\Permission\Cores\AdminPage\AdminPageIdGet;
use Qz\Admin\Permission\Cores\AdminPageOption\AdminPageOptionAdd;
use Qz\Admin\Permission\Cores\AdminPageOption\AdminPageOptionIdGet;
use Qz\Admin\Permission\Cores\AdminRequest\AdminRequestAdd;
use Qz\Admin\Permission\Facades\Access;
use Qz\Admin\Permission\Models\AdminUser;

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
        $param = $request->all();
        $adminPageId = AdminPageIdGet::init()
            ->setCode(Arr::get($param, 'pageCode'))
            ->run()
            ->getId();
        Access::setAdminPageId($adminPageId);
        $adminPageOptionId = AdminPageOptionIdGet::init()
            ->setAdminPageId($adminPageId)
            ->setCode(Arr::get($param, 'optionCode'))
            ->run()
            ->getId();
        if (empty($adminPageOptionId) && Arr::get($param, 'optionCode') && Arr::get($param, 'optionName')) {
            $adminPageOptionId = AdminPageOptionAdd::init()
                ->setAdminPageId($adminPageId)
                ->setCode(Arr::get($param, 'optionCode'))
                ->setName(Arr::get($param, 'optionName'))
                ->setIsShow(Arr::get($param, 'optionCode') != 'list')
                ->run()
                ->getId();
        }
        Access::setAdminPageOptionId($adminPageOptionId);
        $adminRequestId = AdminRequestAdd::init()
            ->setAdminPageOptionId($adminPageOptionId)
            ->setCode(Arr::get($param, 'requestCode'))
            ->setName(Arr::get($param, 'requestName'))
            ->run()
            ->getId();
        Access::setAdminRequestId($adminRequestId);
        $user = Auth::guard('admin')->user();
        if (empty($user) || !$user instanceof AdminUser) {
            return $next($request);
        }
        $user->load('administrator');
        Access::setAdminUserId(Arr::get($user, 'id'));
        Access::setCustomerId(Arr::get($user, 'customer_id'));
        Access::setAdministrator((boolean) Arr::get($user, 'administrator.id'));
        return $next($request);
    }
}
