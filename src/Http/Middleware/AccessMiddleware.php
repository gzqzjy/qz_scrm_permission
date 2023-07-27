<?php

namespace Qz\Admin\Permission\Http\Middleware;

use App\Facades\AdminRequest;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Qz\Admin\Permission\Cores\AdminPage\AdminPageIdGet;
use Qz\Admin\Permission\Cores\AdminPageOption\AdminPageOptionAdd;
use Qz\Admin\Permission\Cores\AdminPageOption\AdminPageOptionIdGet;
use Qz\Admin\Permission\Cores\AdminRequest\AdminRequestAdd;
use Qz\Admin\Permission\Cores\AdminUser\GetAdminUserIdsByAdminUserId;
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
        $param = $request->all();
        $pageId = AdminPageIdGet::init()
            ->setCode(Arr::get($param, 'pageCode'))
            ->run()
            ->getId();
        if ($pageId) {
            AdminRequest::setAdminPageId($pageId);
        }
        $optionId = AdminPageOptionIdGet::init()
            ->setAdminPageId($pageId)
            ->setCode(Arr::get($param, 'optionCode'))
            ->run()
            ->getId();
        if (empty($optionId) && Arr::get($param, 'optionCode') && Arr::get($param, 'optionName')){
            $optionId = AdminPageOptionAdd::init()
                ->setAdminPageId($pageId)
                ->setCode(Arr::get($param, 'optionCode'))
                ->setName(Arr::get($param, 'optionName'))
                ->setIsShow(Arr::get($param, 'optionCode') != 'list')
                ->run()
                ->getId();
        }
        if (!empty($optionId)) {
            AdminRequest::setAdminPageOptionId($optionId);
        }
        if ($optionId && Arr::get($param, 'requestCode') && Arr::get($param, 'requestName')) {
            $requestId = AdminRequestAdd::init()
                ->setAdminPageOptionId($optionId)
                ->setCode(Arr::get($param, 'requestCode'))
                ->setName(Arr::get($param, 'requestName'))
                ->run()
                ->getId();
            if (!empty($requestId)) {
                AdminRequest::setAdminRequestId($requestId);
            }
        }
        $user = Auth::guard('admin')->user();
        if (empty($user)) {
            return $next($request);
        }
        $user->load('administrator');
        Access::setAdminUserId(Arr::get($user, 'id'));
        Access::setCustomerId(Arr::get($user, 'customer_id'));
        Access::setAdministrator((boolean) Arr::get($user, 'administrator.id'));
        return $next($request);
    }
}
