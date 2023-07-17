<?php

namespace Qz\Admin\Permission\Http\Controllers\Admin\Auth;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Qz\Admin\Permission\Cores\AdminPage\AdminPageAdd;
use Qz\Admin\Permission\Cores\AdminPage\AdminPageIdGet;
use Qz\Admin\Permission\Cores\AdminPageColumn\AdminPageColumnAdd;
use Qz\Admin\Permission\Cores\AdminPageOption\AdminPageOptionAdd;
use Qz\Admin\Permission\Cores\AdminPageOption\AdminPageOptionDelete;
use Qz\Admin\Permission\Cores\AdminUser\AdminMenuIdsByAdminUserIdGet;
use Qz\Admin\Permission\Cores\AdminUser\GetPermissionByAdminUserId;
use Qz\Admin\Permission\Facades\Access;
use Qz\Admin\Permission\Http\Controllers\Admin\AdminController;
use Qz\Admin\Permission\Models\AdminMenu;
use Qz\Admin\Permission\Models\AdminPage;
use Qz\Admin\Permission\Models\AdminPageColumn;
use Qz\Admin\Permission\Models\AdminPageOption;
use Qz\Admin\Permission\Models\AdminUser;
use Qz\Admin\Permission\Models\AdminUserDepartment;
use Qz\Admin\Permission\Models\AdminUserPageOption;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Qz\Admin\Permission\Exceptions\MessageException;

class AccessController extends AdminController
{
    public function login()
    {
        $status = 'error';
        $type = 'mobile';
        $mobile = $this->getParam('mobile');
        $token = '';
        $model = AdminUser::query()
            ->where('mobile', $mobile)
            ->where('status', AdminUser::STATUS_WORKING)
            ->orderBy('id')
            ->first();
        if (empty($model)) {
            return $this->json(compact('token', 'status', 'type'));
        }
        if ($model instanceof AdminUser) {
            $model->setConnection(config('database.default'))->tokens()->delete();
            $token = $model->setConnection(config('database.default'))->createToken('admin_user')->plainTextToken;
            if ($token) {
                $status = 'ok';
            }
        }
        return $this->json(compact('token', 'status', 'type'));
    }

    /**
     * @return JsonResponse
     * @throws MessageException
     */
    public function captcha()
    {
        $mobile = $this->getParam('phone');
        if (empty($mobile)) {
            throw new MessageException('手机号不能为空');
        }
        return $this->success();
    }

    public function logout()
    {
        $user = Auth::guard('admin')
            ->user();
        if ($user instanceof AdminUser) {
            $user->setConnection(config('database.default'))->tokens()->delete();
        }
        return $this->success();
    }

    public function user()
    {
        $user = Auth::guard('admin')->user();
        $name = '';
        if ($user instanceof AdminUser) {
            $id = Arr::get($user, 'id', '');
            $name = Arr::get($user, 'name', '');
            $administrator = $this->isAdministrator();
        }
        return $this->success(compact('id', 'name', 'administrator'));
    }

    public function addPage()
    {
        $pageId = AdminPageAdd::init()
            ->setName($this->getParam('page_name'))
            ->setCode($this->getParam('page_code'))
            ->run()
            ->getId();
        return $this->success(compact('pageId'));
    }

    public function columns()
    {
        $pageId = AdminPageIdGet::init()
            ->setCode($this->getParam('page_code'))
            ->run()
            ->getId();
        $columns = $this->getParam('columns');
        $pageColumnIds = [];
        foreach ($columns as $column) {
            $dataIndex = Arr::get($column, 'data_index');
            if (is_array($dataIndex)) {
                $dataIndex = implode('.', $dataIndex);
            }
            $pageColumnId = AdminPageColumnAdd::init()
                ->setAdminPageId($pageId)
                ->setCode($dataIndex)
                ->setName(Arr::get($column, 'title'))
                ->run()
                ->getId();
            if ($pageColumnId) {
                $pageColumnIds = Arr::prepend($pageColumnIds, $pageColumnId);
            }
        }
        $model = AdminPageColumn::query()
            ->whereIn('id', $pageColumnIds);
        if (!Access::getAdministrator()) {
            $adminPageColumnIds = GetPermissionByAdminUserId::init()
                ->setAdminUserId(Access::getAdminUserId())
                ->run()
                ->getAdminPageColumnIds();
            $model->whereIn('id', array_intersect($pageColumnIds, $adminPageColumnIds));
        }
        $dataIndexes = $model
            ->pluck('code');
        return $this->json(compact('dataIndexes'));
    }

    /**
     * @return JsonResponse
     * @throws MessageException
     */
    public function option()
    {
        $access = false;
        $validator = Validator::make($this->getParam(), [
            'page_code' => [
                'required',
                Rule::exists(AdminPage::class, 'code')
                    ->withoutTrashed(),
            ],
            'option_code' => [
                'required',
            ],
            'option_name' => [
                'required',
            ],
        ], [
            'page_code.required' => '页面标识不能为空',
            'page_code.exists' => '页面标识不存在',
            'option_code.required' => '页面操作标识不能为空',
            'option_name.required' => '页面操作标识不能为空',
        ]);
        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }
        $pageId = AdminPageIdGet::init()
            ->setCode($this->getParam('page_code'))
            ->run()
            ->getId();
        if (empty($pageId)) {
            return $this->json($access);
        }
        $pageOptionId = AdminPageOptionAdd::init()
            ->setAdminPageId($pageId)
            ->setCode($this->getParam('option_code'))
            ->setName($this->getParam('option_name'))
            ->run()
            ->getId();
        if (empty($pageOptionId)) {
            return $this->json($access);
        }
        if (Access::getAdministrator()) {
            $access = true;
            return $this->json($access);
        }
        $access = AdminUserPageOption::query()
            ->whereHas('adminUser', function (Builder $builder) {
                $builder->where('admin_user_id', Auth::guard('admin')->id());
            })
            ->where('admin_page_option_id', $pageOptionId)
            ->exists();
        return $this->json($access);
    }

    /**
     * @return JsonResponse
     * @throws MessageException
     */
    public function options()
    {
        $validator = Validator::make($this->getParam(), [
            'page_code' => [
                'required',
                Rule::exists(AdminPage::class, 'code')
                    ->withoutTrashed(),
            ],
        ], [
            'page_code.required' => '页面标识不能为空',
            'page_code.exists' => '页面标识不存在',
        ]);
        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }
        $pageId = AdminPageIdGet::init()
            ->setCode($this->getParam('page_code'))
            ->run()
            ->getId();
        if (empty($pageId)) {
            return $this->success();
        }
        $adminPageOptions = AdminPageOption::query()
            ->select('id')
            ->where('admin_page_id', $pageId)
            ->get();
        foreach ($adminPageOptions as $adminPageOption) {
            AdminPageOptionDelete::init()
                ->setId(Arr::get($adminPageOption, 'id'))
                ->run();
        }
        $options = $this->getParam('options');
        if (empty($options)) {
            return $this->success();
        }
        $pageOptionIds = [];
        foreach ($options as $option) {
            $pageOptionId = AdminPageOptionAdd::init()
                ->setAdminPageId($pageId)
                ->setCode(Arr::get($option, 'code'))
                ->setName(Arr::get($option, 'name'))
                ->run()
                ->getId();
            if ($pageOptionId) {
                $pageOptionIds = Arr::prepend($pageOptionIds, $pageOptionId);
            }
        }
        $model = AdminPageOption::query()
            ->whereIn('id', $pageOptionIds);
        if (!Access::getAdministrator()) {
            $adminPageOptionIds = GetPermissionByAdminUserId::init()
                ->setAdminUserId(Access::getAdminUserId())
                ->run()
                ->getAdminPageOptionIds();
            $model->whereIn('id', array_intersect($pageOptionIds, $adminPageOptionIds));
        }
        $dataIndexes = $model
            ->pluck('code');
        return $this->json(compact('dataIndexes'));
    }

    public function menu()
    {
        $model = AdminMenu::query()
            ->where('parent_id', 0);
        $administrator = $this->isAdministrator();
        $adminMenuIds = [];
        $menus = [];
        if (empty($administrator)) {
            $adminMenuIds = (array) AdminMenuIdsByAdminUserIdGet::init()
                ->setAdminUserId($this->getLoginAdminUserId())
                ->run()
                ->getAdminMenuIds();
        }
        $model = $model->orderByDesc('sort')
            ->get();
        $model->load([
            'children',
        ]);
        foreach ($model as $value) {
            if ($menu = $this->menuItem($value, $administrator, $adminMenuIds)) {
                $menus[] = $menu;
            }
        }
        return $this->json($menus);
    }

    protected function menuItem($value, $administrator, $adminMenuIds)
    {
        if (empty($administrator) && !in_array(Arr::get($value, 'id'), $adminMenuIds)) {
            return [];
        }
        $route = Arr::get($value, 'config');
        $route = Arr::add($route, 'name', Arr::get($value, 'name'));
        $route = Arr::add($route, 'path', Arr::get($value, 'path'));
        if (Arr::get($value, 'children')) {
            $routes = [];
            $children = Arr::get($value, 'children');
            foreach ($children as $child) {
                if ($itemRoute = $this->menuItem($child, $administrator, $adminMenuIds)) {
                    $routes[] = $itemRoute;
                }
            }
            if (!empty($routes)) {
                Arr::set($route, 'routes', $routes);
            }
        }
        return $route;
    }
}
