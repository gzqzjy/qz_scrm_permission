<?php

namespace Qz\Admin\Permission\Http\Controllers\Admin\AdminUserCustomerSubsystem;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Qz\Admin\Permission\Cores\AdminUser\AdminUserAdd;
use Qz\Admin\Permission\Cores\AdminUserCustomerSubsystem\AdminUserCustomerSubsystemAdd;
use Qz\Admin\Permission\Cores\AdminUserCustomerSubsystem\AdminUserCustomerSubsystemDelete;
use Qz\Admin\Permission\Cores\AdminUserCustomerSubsystem\AdminUserCustomerSubsystemUpdate;
use Qz\Admin\Permission\Cores\AdminUserCustomerSubsystemMenu\AdminUserCustomerSubsystemMenuSync;
use Qz\Admin\Permission\Exceptions\MessageException;
use Qz\Admin\Permission\Facades\Access;
use Qz\Admin\Permission\Http\Controllers\Admin\AdminController;
use Qz\Admin\Permission\Models\AdminMenu;
use Qz\Admin\Permission\Models\AdminUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystem;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystemMenu;

class AdminUserCustomerSubsystemController extends AdminController
{
    public function get()
    {
        $model = AdminUserCustomerSubsystem::query()
            ->where('customer_subsystem_id', Access::getCustomerSubsystemId());
        $model = $this->filter($model);
        $model = $model->paginate($this->getPageSize());
        call_user_func_array([$model, 'load'], [
            'adminUser',
            'adminUserCustomerSubsystemMenus',
            'adminUserCustomerSubsystemMenus.adminMenu',
            'adminUserCustomerSubsystemMenus.adminMenu.child',
            'adminUserCustomerSubsystemMenus.adminMenu.parentData'
        ]);
        foreach ($model as $value) {
            $menuIds = [];
            $adminUserCustomerSubsystemMenus = Arr::get($value, 'adminUserCustomerSubsystemMenus');
            foreach ($adminUserCustomerSubsystemMenus as $adminUserCustomerSubsystemMenu) {
                if (!count(Arr::get($adminUserCustomerSubsystemMenu, 'adminMenu.child'))) {
                    $menuIds[] = $this->getParentId(Arr::get($adminUserCustomerSubsystemMenu, 'adminMenu'));
                }
            }
            Arr::set($value, 'menu_ids', $menuIds);
        }
        return $this->page($model);
    }

    protected function getParentId($data, $ids = [])
    {
        if (Arr::get($data, 'id')) {
            $ids = Arr::prepend($ids, Arr::get($data, 'id'));
        }
        if (Arr::get($data, 'parentData')) {
            $ids = $this->getParentId(Arr::get($data, 'parentData'), $ids);
        }
        return $ids;
    }

    /**
     * @return JsonResponse
     * @throws MessageException
     */
    public function store()
    {
        $validator = Validator::make($this->getParam('admin_user'), [
            'name' => [
                'required',
            ],
            'mobile' => [
                'required',
                Rule::unique(AdminUser::class, 'mobile')
                    ->withoutTrashed(),
            ],
        ], [
            'name.required' => '员工名不能为空',
            'mobile.required' => '员工手机号不能为空',
            'mobile.unique' => '员工手机号不能重复',
        ]);
        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }
        $adminUserId = AdminUserAdd::init()
            ->setMobile($this->getParam('mobile'))
            ->setName($this->getParam('name'))
            ->run()
            ->getId();
        $id = AdminUserCustomerSubsystemAdd::init()
            ->setAdminUserId($adminUserId)
            ->setAdministrator(false)
            ->setStatus(AdminUserCustomerSubsystem::STATUS_NORMAL)
            ->setCustomerSubsystemId(Access::getCustomerSubsystemId())
            ->run()
            ->getId();
        return $this->success(compact('id'));
    }

    /**
     * @return JsonResponse
     * @throws MessageException
     */
    public function update()
    {
        $model = AdminUserCustomerSubsystem::query()
            ->find($this->getParam('id'));
        $validator = Validator::make($this->getParam('admin_user'), [
            'id' => [
                'required',
                Rule::exists(AdminUserCustomerSubsystem::class)
                    ->withoutTrashed(),
            ],
            'mobile' => [
                Rule::unique(AdminUser::class)
                    ->withoutTrashed()
                    ->ignore(Arr::get($model, 'admin_user_id'))
            ],
        ], [
            'mobile.unique' => '员工手机号不能重复',
            'id.required' => 'ID不能为空',
            'id.exists' => 'ID不存在',
        ]);
        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }
        $adminUserId = AdminUserAdd::init()
            ->setMobile($this->getParam('mobile'))
            ->setName($this->getParam('name'))
            ->run()
            ->getId();
        $id = AdminUserCustomerSubsystemUpdate::init()
            ->setAdminUserId($adminUserId)
            ->setId($this->getParam('id'))
            ->run()
            ->getId();
        return $this->success(compact('id'));
    }

    public function destroy()
    {
        $id = $this->getParam('id');
        if (is_array($id)) {
            foreach ($id as $value) {
                AdminUserCustomerSubsystemDelete::init()
                    ->setId($value)
                    ->run()
                    ->getId();
            }
            return $this->success();
        }
        AdminUserCustomerSubsystemDelete::init()
            ->setId($id)
            ->run()
            ->getId();
        return $this->success();
    }

    public function all()
    {
        $param = $this->getParam();
        $select = Arr::get($param, 'select', 'id as value, name as label');
        $model = AdminUserCustomerSubsystem::query()
            ->where('customer_subsystem_id', Access::getCustomerSubsystemId())
            ->selectRaw($select);
        $model = $this->filter($model);
        $model = $model->get();
        return $this->response($model);
    }

    public function menus()
    {
        $model = AdminMenu::query()
            ->where('parent_id', 0);
        $administrator = $this->isAdministrator();
        if (empty($administrator)) {
            $model->whereHas('adminUserCustomerSubsystemMenus', function (Builder $builder) {
                $builder->whereHas('adminUserCustomerSubsystem', function (Builder $builder) {
                    $builder->where('admin_user_id', $this->getLoginAdminUserId());
                });
            });
        }
        $model = $model->get();
        $model->load([
            'children',
        ]);
        $model = $model->toArray();
        $menus = [];
        foreach ($model as $value) {
            $menus[] = $this->menuItem($value);
        }
        return $this->response($menus);
    }

    protected function menuItem($value)
    {
        $data = [];
        Arr::set($data, 'label', Arr::get($value, 'name'));
        Arr::set($data, 'value', Arr::get($value, 'id'));
        if (Arr::get($value, 'children')) {
            $routes = [];
            $children = Arr::get($value, 'children');
            foreach ($children as $child) {
                $routes[] = $this->menuItem($child);
            }
            if (!empty($routes)) {
                Arr::set($data, 'children', $routes);
            }
        }
        return $data;
    }

    public function addMenus()
    {
        $validator = Validator::make($this->getParam(), [
            'id' => [
                'required',
                Rule::exists(AdminUserCustomerSubsystem::class)
                    ->withoutTrashed(),
            ],
        ], [
            'id.required' => 'ID不能为空',
            'id.exists' => 'ID不存在',
        ]);
        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }
        $id = $this->getParam('id');
        $menuIds = Arr::collapse($this->getParam('menu_ids'));
        $menuIds = array_unique($menuIds);
        AdminUserCustomerSubsystemMenuSync::init()
            ->setAdminUserCustomerSubsystemId($id)
            ->setAdminMenuIds($menuIds)
            ->run();
        return $this->success();
    }
}
