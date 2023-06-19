<?php

namespace Qz\Admin\Permission\Http\Controllers\Admin\AdminUser;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Qz\Admin\Permission\Cores\AdminMenu\GetTreeAdminMenusWithCheck;
use Qz\Admin\Permission\Cores\AdminRole\GetMenuByAdminRole;
use Qz\Admin\Permission\Cores\AdminUser\AdminUserAdd;
use Qz\Admin\Permission\Cores\AdminUser\AdminUserDelete;
use Qz\Admin\Permission\Cores\AdminUser\AdminUserUpdate;
use Qz\Admin\Permission\Cores\AdminUserCustomerSubsystem\AdminUserCustomerSubsystemUpdatePermission;
use Qz\Admin\Permission\Cores\AdminUserCustomerSubsystem\GetPermissionByAdminUserCustomerSubsystemId;
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
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystemRole;

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
        if ($this->getParam('admin_department_id')) {
            $model = $model->whereHas('adminUserCustomerSubsystems', function (Builder $builder) {
                $builder->whereHas('adminUserCustomerSubsystemDepartments', function (Builder $builder) {
                    $builder->where('admin_department_id', $this->getParam('admin_department_id'));
                });
            });
        }
        $model = $model->get();
        $model->load([
            'adminUserCustomerSubsystems' => function (HasMany $hasMany) {
                $hasMany->where('customer_subsystem_id', Access::getCustomerSubsystemId());
            },
            'adminUserCustomerSubsystems.adminUserCustomerSubsystemDepartments',
            'adminUserCustomerSubsystems.adminUserCustomerSubsystemRoles'
        ]);
        $model->append(['statusDesc']);
        //call_user_func([$model, 'append'], ['status_desc']);
        foreach ($model as &$item) {
            $adminDepartments = Arr::get($item, 'adminUserCustomerSubsystems.0.adminUserCustomerSubsystemDepartments');
            $adminRoles = Arr::get($item, 'adminUserCustomerSubsystems.0.adminUserCustomerSubsystemRoles');
            $department = [];
            foreach ($adminDepartments as $adminDepartment) {
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
            'id' => [
                'required',
                Rule::exists('admin_users')
                    ->withoutTrashed(),
            ],
            'mobile' => [
                Rule::unique(AdminUser::class)
                    ->withoutTrashed()
                    ->ignore($this->getParam('id'))
            ],
        ], [
            'id.required' => '请选择员工',
            'id.exists' => '员工id不存在',
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

    /**
     * @return JsonResponse
     * @throws MessageException
     */
    public function updatePermission()
    {
        $validator = Validator::make($this->getParam(), [
            'id' => [
                'required',
                Rule::exists('admin_users')
                    ->withoutTrashed()
            ],
            'permission' => [
                'required',
                'array'
            ],
        ], [
            'id.required' => '请选择员工',
            'id.exists' => '员工id不存在',
            'permission.required' => '请选择权限',
            'permission.array' => '权限格式有误',
        ]);
        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }
        $adminUserCustomerSubsystemId = AdminUserCustomerSubsystem::query()
            ->where('admin_user_id', $this->getParam('id'))
            ->where('customer_subsystem_id', Access::getCustomerSubsystemId())
            ->value('id');
        if (empty($adminUserCustomerSubsystemId)) {
            throw new MessageException("该员工找不到子系统");
        }
        $adminRoleMenuIds = $adminRolePageColumnIds = $adminRolePageOptionIds = $adminMenus = $adminPageColumns = $adminPageOptions = [];

        $adminRoleIds = AdminUserCustomerSubsystemRole::query()
            ->where('admin_user_customer_subsystem_id', $adminUserCustomerSubsystemId)
            ->pluck('admin_role_id')
            ->toArray();
        if ($adminRoleIds) {
            $rolePermission = GetMenuByAdminRole::init()
                ->setAdminRoleIds($adminRoleIds);
            $adminRoleMenuIds = $rolePermission->getAdminMenuIds();
            $adminRolePageColumnIds = $rolePermission->getAdminPageColumnIds();
            $adminRolePageOptionIds = $rolePermission->getAdminPageOptionIds();
        }

        $add = [
            'adminMenuIds' => [],
            'adminPageColumnIds' => [],
            'adminPageOptionIds' => [],
        ];
        $delete = [
            'adminMenuIds' => [],
            'adminPageColumnIds' => [],
            'adminPageOptionIds' => [],
        ];
        $permissions = $this->getParam('permission');
        foreach ($permissions as $permission) {
            list($addItem, $deleteItem) = $this->getPermission($permission);
            $addItem['adminMenuIds'] && $add['adminMenuIds'] = array_merge($addItem['adminMenuIds'], $add['adminMenuIds']);
            $addItem['adminPageColumnIds'] && $add['adminPageColumnIds'] = array_merge($addItem['adminPageColumnIds'], $add['adminPageColumnIds']);
            $addItem['adminPageOptionIds'] && $add['adminPageOptionIds'] = array_merge($addItem['adminPageOptionIds'], $add['adminPageOptionIds']);
            $deleteItem['adminMenuIds'] && $delete['adminMenuIds'] = array_merge($deleteItem['adminMenuIds'], $delete['adminMenuIds']);
            $deleteItem['adminPageColumnIds'] && $delete['adminPageColumnIds'] = array_merge($deleteItem['adminPageColumnIds'], $delete['adminPageColumnIds']);
            $deleteItem['adminPageOptionIds'] && $delete['adminPageOptionIds'] = array_merge($deleteItem['adminPageOptionIds'], $delete['adminPageOptionIds']);

        }
        if ($adminRoleMenuIds && $add['adminMenuIds']) {
            $adminMenuIds = array_diff($add['adminMenuIds'], $adminRoleMenuIds);

            if ($adminMenuIds) {
                $adminMenus = array_map(function ($adminMenuId) {
                    return [
                        'id' => $adminMenuId,
                        'type' => 'add',
                    ];
                }, $adminMenuIds);
            }
        }

        if ($adminRolePageColumnIds && $add['adminPageColumnIds']) {
            $adminPageColumnIds = array_diff($add['adminPageColumnIds'], $adminRolePageColumnIds);
            if ($adminPageColumnIds) {
                $adminPageColumns = array_map(function ($adminPageColumnId) {
                    return [
                        'id' => $adminPageColumnId,
                        'type' => 'add',
                    ];
                }, $adminPageColumnIds);
            }
        }
        if ($adminRolePageOptionIds && $add['adminPageOptionIds']) {
            $adminPageOptionIds = array_diff($add['adminPageOptionIds'], $adminRolePageOptionIds);
            if ($adminPageOptionIds) {
                $adminPageOptions = array_map(function ($adminPageOptionId) {
                    return [
                        'id' => $adminPageOptionId,
                        'type' => 'add',
                    ];
                }, $adminPageOptionIds);
            }
        }
        if ($adminRoleMenuIds && $delete['adminMenuIds']) {
            $adminMenuIds = array_intersect($delete['adminMenuIds'], $adminRoleMenuIds);
            if ($adminMenuIds) {
                $adminMenus = array_merge(array_map(function ($adminMenuId) {
                    return [
                        'id' => $adminMenuId,
                        'type' => 'delete',
                    ];
                }, $adminMenuIds), $adminMenus);
            }
        }
        if ($adminRolePageColumnIds && $delete['adminPageColumnIds']) {
            $adminPageColumnIds = array_intersect($delete['adminPageColumnIds'], $adminRolePageColumnIds);
            if ($adminPageColumnIds) {
                $adminPageColumns = array_merge(array_map(function ($adminPageColumnId) {
                    return [
                        'id' => $adminPageColumnId,
                        'type' => 'delete',
                    ];
                }, $adminPageColumnIds), $adminPageColumns);
            }
        }
        if ($adminRolePageOptionIds && $delete['adminPageOptionIds']) {
            $adminPageOptionIds = array_intersect($delete['adminPageOptionIds'], $adminRolePageOptionIds);
            if ($adminPageOptionIds) {
                $adminPageOptions = array_merge(array_map(function ($adminPageOptionId) {
                    return [
                        'id' => $adminPageOptionId,
                        'type' => 'delete',
                    ];
                }, $adminPageOptionIds), $adminPageOptions);
            }
        }
        $id = AdminUserCustomerSubsystemUpdatePermission::init()
            ->setId($adminUserCustomerSubsystemId)
            ->setAdminMenu($adminMenus)
            ->setAdminPageColumn($adminPageColumns)
            ->setAdminPageOption($adminPageOptions)
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

    public function allStatus()
    {
        $statusDesc = AdminUser::STATUS_DESC;
        $data = [];
        foreach ($statusDesc as $value => $label) {
            $data[] = compact('value', 'label');
        }
        return $this->response($data);
    }

    public function allSex()
    {
        $statusDesc = AdminUser::SEX_DESC;
        $data = [];
        foreach ($statusDesc as $value => $label) {
            $data[] = compact('value', 'label');
        }
        return $this->response($data);
    }


    public function permission()
    {
        $validator = Validator::make($this->getParam(), [
            'id' => [
                'required',
                Rule::exists('admin_users')
                    ->withoutTrashed()
            ]
        ], [
            'id.required' => '请选择员工',
            'id.exists' => '员工id不存在',
        ]);
        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }

        $adminUserCustomerSubsystemId = AdminUserCustomerSubsystem::query()
            ->where('admin_user_id', $this->getParam('id'))
            ->where('customer_subsystem_id', Access::getCustomerSubsystemId())
            ->value('id');
        if (empty($adminUserCustomerSubsystemId)) {
            throw new MessageException("该员工找不到子系统");
        }
        //获取用户的权限
        $model = AdminMenu::query();
        $administrator = $this->isAdministrator();
        $adminMenuIds = $adminPageOptionIds = $adminPageColumnIds = [];
        if (empty($administrator)) {
            //获取用户的角色
            $permission = GetPermissionByAdminUserCustomerSubsystemId::init()
                ->setAdminUserCustomerSubsystemId(Access::getAdminUserCustomerSubsystemId())
                ->run();
            $adminMenuIds = $permission->getAdminMenuIds();
            $adminPageOptionIds = $permission->getAdminPageOptionIds();
            $adminPageColumnIds = $permission->getAdminPageColumnIds();
            $model->whereIn('id', $adminMenuIds);
//            $model->whereHas('adminUserCustomerSubsystemMenus', function (Builder $builder) {
//                $builder->whereHas('adminUserCustomerSubsystem', function (Builder $builder) {
//                    $builder->where('admin_user_id', $this->getLoginAdminUserId());
//                });
//            });
        } else {
            $excludeMenuId = AdminMenu::query()
                ->where('name', '系统设置')
                ->where('parent_id', 0)
                ->value('id');//系统设置不显示
            $model = $model->where('parent_id', 0)
                ->where(function (Builder $builder) use ($excludeMenuId){
                    if ($excludeMenuId){
                        $builder->where('id', '<>', $excludeMenuId);
                    }
                });;
        }
        $model = $model->get();
        $model->load([
            'children',
            'adminPage',
            'adminPage.adminPageOptions',
            'adminPage.adminPageColumns',
        ]);
        $model = $model->toArray();//登录用户所有的权限
        $data = $existMenuIds = [];
        foreach ($model as $value) {
            if ($item = $this->item($value, $adminMenuIds, $adminPageOptionIds, $adminPageColumnIds, $existMenuIds)) {
                $data[] = $item;
            }
        }

        //选择的员工所有的权限
        $permission = GetPermissionByAdminUserCustomerSubsystemId::init()
            ->setAdminUserCustomerSubsystemId($adminUserCustomerSubsystemId)
            ->run();
        //对比登录员工权限+选择员工拥有的权限
        $menus = GetTreeAdminMenusWithCheck::init()
            ->setAdminMenus($data)
            ->setAdminMenuIds($permission->getAdminMenuIds())
            ->setAdminPageColumnIds($permission->getAdminPageColumnIds())
            ->setAdminPageOptionIds($permission->getAdminPageOptionIds())
            ->run()
            ->getTreeAdminMenus();

        return $this->success($menus);
    }

    protected function item($value, $adminMenuIds, $adminPageOptionIds, $adminPageColumnIds, &$existMenuIds)
    {
        if (empty($this->isAdministrator()) && (!in_array(Arr::get($value, 'id'), $adminMenuIds) || in_array(Arr::get($value, 'id'), $existMenuIds))) {
            return [];
        }
        $existMenuIds[] = Arr::get($value, 'id');
        if (empty($this->isAdministrator()) && $adminPageOptions = Arr::get($value, 'admin_page.admin_page_options')) {
            $adminPageOptions = array_values(array_filter($adminPageOptions, function ($adminPageOption) use ($adminPageOptionIds) {
                if (in_array(Arr::get($adminPageOption, 'id'), $adminPageOptionIds)) {
                    return true;
                }
            }));
            Arr::set($value, 'admin_page.admin_page_options', $adminPageOptions);
        }
        if (empty($this->isAdministrator()) && $adminPageColumns = Arr::get($value, 'admin_page.admin_page_columns')) {
            $oldAdminPageColumns = $adminPageColumns;
            $adminPageColumns = array_values(array_filter($adminPageColumns, function ($adminPageColumn) use ($adminPageColumnIds) {
                if (in_array(Arr::get($adminPageColumn, 'id'), $adminPageColumnIds)) {
                    return true;
                }
            }));
            Arr::set($value, 'admin_page.admin_page_columns', $adminPageColumns);
        }
        $data = Arr::except($value, 'children');
        if (Arr::get($value, 'children')) {
            $routes = [];
            $children = Arr::get($value, 'children');
            foreach ($children as $child) {
                if ($item = $this->item($child, $adminMenuIds, $adminPageOptionIds, $adminPageColumnIds, $existMenuIds)) {
                    $routes[] = $item;
                }

            }
            if (!empty($routes)) {
                Arr::set($data, 'children', $routes);
            }
        }

        Log::info('返回菜单data', [$data]);
        return $data;
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

    protected function getPermission($permission)
    {
        $add = [
            'adminMenuIds' => [],
            'adminPageColumnIds' => [],
            'adminPageOptionIds' => [],
        ];
        $delete = [
            'adminMenuIds' => [],
            'adminPageColumnIds' => [],
            'adminPageOptionIds' => [],
        ];
        Arr::get($permission, 'check') ? $add['adminMenuIds'][] = Arr::get($permission, 'value') : $delete['adminMenuIds'][] = Arr::get($permission, 'value');

        if ($columns = Arr::get($permission, 'columns')) {
            foreach ($columns as $column) {
                $id = Str::replace("column_", "", Arr::get($column, 'value'));
                Arr::get($column, 'check') ? $add['adminPageColumnIds'][] = $id : $delete['adminPageColumnIds'][] = $id;
            }
        }
        if ($options = Arr::get($permission, 'options')) {
            foreach ($options as $option) {
                $id = Str::replace("option_", "", Arr::get($option, 'value'));
                Arr::get($option, 'check') ? $add['adminPageOptionIds'][] = $id : $delete['adminPageOptionIds'][] = $id;
            }
        }
        if ($children = Arr::get($permission, 'children')) {
            foreach ($children as $child) {
                list($addItem, $deleteItem) = $this->getPermission($child);
                $addItem['adminMenuIds'] && $add['adminMenuIds'] = array_merge($addItem['adminMenuIds'], $add['adminMenuIds']);
                $addItem['adminPageColumnIds'] && $add['adminPageColumnIds'] = array_merge($addItem['adminPageColumnIds'], $add['adminPageColumnIds']);
                $addItem['adminPageOptionIds'] && $add['adminPageOptionIds'] = array_merge($addItem['adminPageOptionIds'], $add['adminPageOptionIds']);
                $deleteItem['adminMenuIds'] && $delete['adminMenuIds'] = array_merge($deleteItem['adminMenuIds'], $delete['adminMenuIds']);
                $deleteItem['adminPageColumnIds'] && $delete['adminPageColumnIds'] = array_merge($deleteItem['adminPageColumnIds'], $delete['adminPageColumnIds']);
                $deleteItem['adminPageOptionIds'] && $delete['adminPageOptionIds'] = array_merge($deleteItem['adminPageOptionIds'], $delete['adminPageOptionIds']);

            }
        }
        return [$add, $delete];
    }
}
