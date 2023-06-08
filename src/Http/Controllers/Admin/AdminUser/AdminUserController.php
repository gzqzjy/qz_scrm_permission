<?php

namespace Qz\Admin\Permission\Http\Controllers\Admin\AdminUser;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Qz\Admin\Permission\Cores\AdminUser\AdminUserAdd;
use Qz\Admin\Permission\Cores\AdminUser\AdminUserDelete;
use Qz\Admin\Permission\Cores\AdminUser\AdminUserUpdate;
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

class AdminUserController extends AdminController
{
    public function get()
    {
        $model = AdminUser::query()
            ->whereHas('adminUserCustomerSubsystems', function (Builder $builder) {
                $builder->where('customer_subsystem_id', Access::getCustomerSubsystemId());
//                $builder->whereHas('customerSubsystem', function (Builder $builder) {
//                    $builder->where('subsystem_id', Access::getSubsystemId());
//                });
            });
        $model = $this->filter($model);
        if ($this->getParam('admin_department_id')){
            $model = $model->whereHas('adminUserCustomerSubsystems',function (Builder $builder){
                $builder->whereHas('adminUserCustomerSubsystemDepartments', function (Builder $builder) {
                    $builder->where('admin_department_id', $this->getParam('admin_department_id'));
                });
            });
        }
        $model = $model->get();
        $model->load([
            'adminUserCustomerSubsystems' => function(HasMany $hasMany){
                $hasMany->where('customer_subsystem_id', Access::getCustomerSubsystemId());
            },
            'adminUserCustomerSubsystems.adminUserCustomerSubsystemDepartments',
            'adminUserCustomerSubsystems.adminUserCustomerSubsystemRoles'
        ]);
        $model->append(['statusDesc']);
        //call_user_func([$model, 'append'], ['status_desc']);
        foreach ($model as &$item){
            $adminDepartments = Arr::get($item, 'adminUserCustomerSubsystems.0.adminUserCustomerSubsystemDepartments');
            $adminRoles = Arr::get($item, 'adminUserCustomerSubsystems.0.adminUserCustomerSubsystemRoles');
            $department = [];
            foreach ($adminDepartments as $adminDepartment){
                $department[] = [
                    'id' => Arr::get($adminDepartment, 'admin_department_id'),
                    'administrator' => Arr::get($adminDepartment, 'administrator')
                ];
            }
            $item->admin_departments = $department;
            $item->admin_role_ids = Arr::pluck($adminRoles, 'id');
        }
        return $this->success($model->toArray());
    }

    /**
     * @return JsonResponse
     * @throws MessageException
     */
    public function store()
    {
        $validator = Validator::make($this->getParam(), [
            'name' => [
                'required',
            ],
            'mobile' => [
                'required',
                Rule::unique(AdminUser::class)
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
        $this->addParam('customer_subsystem_id', Access::getCustomerSubsystemId());
        $id = AdminUserAdd::init()
            ->setParam($this->getParam())
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
        $validator = Validator::make($this->getParam(), [
            'mobile' => [
                Rule::unique(AdminUser::class)
                    ->withoutTrashed()
                    ->ignore($this->getParam('id'))
            ],
        ], [
            'mobile.unique' => '员工手机号不能重复',
        ]);
        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }
        $this->addParam('customer_subsystem_id', Access::getCustomerSubsystemId());
        $id = AdminUserUpdate::init()
            ->setId($this->getParam('id'))
            ->setParam($this->getParam())
            ->run()
            ->getId();
        return $this->success(compact('id'));
    }

    public function destroy()
    {
        $id = $this->getParam('id');
        if (is_array($id)) {
            foreach ($id as $value) {
                AdminUserDelete::init()
                    ->setId($value)
                    ->run()
                    ->getId();
            }
            return $this->success();
        }
        AdminUserDelete::init()
            ->setId($id)
            ->run()
            ->getId();
        return $this->success();
    }

    public function all()
    {
        $param = $this->getParam();
        $select = Arr::get($param, 'select', 'id as value, name as label');
        $model = AdminUser::query()
            ->whereHas('adminUserCustomerSubsystems', function (Builder $builder) {
                $builder->whereHas('customerSubsystem', function (Builder $builder) {
                    $builder->where('subsystem_id', Access::getSubsystemId());
                });
            })
            ->selectRaw($select);
        $model = $this->filter($model);
        $model = $model->get();
        return $this->response($model);
    }

    public function permission()
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
            'adminPage',
            'adminPage.adminPageOptions',
            'adminPage.adminPageColumns',
        ]);
        $model = $model->toArray();
        $menus = [];
        foreach ($model as $value) {
            $menus[] = $this->permissionItem($value);
        }
        return $this->response($menus);
    }

    protected function permissionItem($value)
    {
        $data = [];
        Arr::set($data, 'label', Arr::get($value, 'name'));
        Arr::set($data, 'value', Arr::get($value, 'id'));
        if (Arr::get($value, 'admin_page_id')) {
            Arr::set($data, 'admin_page_id', Arr::get($value, 'admin_page_id'));
        }
        $adminPageOptions = Arr::get($value, 'admin_page.admin_page_options');
        if (!empty($adminPageOptions)) {
            Arr::set($data, 'options', array_map(function ($option) {
                return [
                    'label' => Arr::get($option, 'name'),
                    'value' => Arr::get($option, 'id'),
                ];
            }, $adminPageOptions));
        }
        $adminPageColumns = Arr::get($value, 'admin_page.admin_page_columns');
        if (!empty($adminPageColumns)) {
            Arr::set($data, 'columns', array_map(function ($column) {
                return [
                    'label' => Arr::get($column, 'name'),
                    'value' => Arr::get($column, 'id'),
                ];
            }, $adminPageColumns));
        }
        if (Arr::get($value, 'children')) {
            $routes = [];
            $children = Arr::get($value, 'children');
            foreach ($children as $child) {
                $routes[] = $this->permissionItem($child);
            }
            if (!empty($routes)) {
                Arr::set($data, 'children', $routes);
            }
        }
        return $data;
    }

    public function addMenus()
    {
        $id = $this->getParam('id');
        $adminUserCustomerSubsystem = AdminUserCustomerSubsystem::query()
            ->where('admin_user_id', $id)
            ->where('customer_subsystem_id', Access::getCustomerSubsystemId())
            ->first();
        if (empty($adminUserCustomerSubsystem)) {
            return $this->success();
        }
        $menuIds = Arr::collapse($this->getParam('menu_ids'));
        $menuIds = array_unique($menuIds);
        AdminUserCustomerSubsystemMenuSync::init()
            ->setAdminUserCustomerSubsystemId(Arr::get($adminUserCustomerSubsystem, 'id'))
            ->setAdminMenuIds($menuIds)
            ->run();
        return $this->success();
    }
}
