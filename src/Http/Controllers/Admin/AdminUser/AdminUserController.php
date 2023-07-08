<?php

namespace Qz\Admin\Permission\Http\Controllers\Admin\AdminUser;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Qz\Admin\Permission\Cores\AdminDepartment\GetInfoByAdminUserCustomerSubsystemId;
use Qz\Admin\Permission\Cores\AdminDepartment\GetTreeCheckDepartmentWithAdminUserCustomerSubsystem;
use Qz\Admin\Permission\Cores\AdminDepartment\GetTreeDepartmentList;
use Qz\Admin\Permission\Cores\AdminMenu\GetTreeAdminMenusWithCheck;
use Qz\Admin\Permission\Cores\AdminUser\AdminUserAdd;
use Qz\Admin\Permission\Cores\AdminUser\AdminUserDelete;
use Qz\Admin\Permission\Cores\AdminUser\AdminUserUpdate;
use Qz\Admin\Permission\Cores\AdminUserCustomerSubsystem\AdminUserCustomerSubsystemUpdatePermission;
use Qz\Admin\Permission\Cores\AdminUserCustomerSubsystem\GetAdminUserCustomerSubsystemIdsByAdminUserCustomerSubsystemIdAndType;
use Qz\Admin\Permission\Cores\AdminUserCustomerSubsystem\GetAdminUserIdsByAdminUserCustomerSubsystemId;
use Qz\Admin\Permission\Cores\AdminUserCustomerSubsystem\GetDataPermissionByAdminUserCustomerSubsystemId;
use Qz\Admin\Permission\Cores\AdminUserCustomerSubsystem\GetFeaturePermissionByAdminUserCustomerSubsystemId;
use Qz\Admin\Permission\Cores\AdminUserCustomerSubsystem\GetPermissionByAdminUserCustomerSubsystemId;
use Qz\Admin\Permission\Cores\AdminUserCustomerSubsystem\GetSubAdminDepartmentIdsByAdminDepartmentIds;
use Qz\Admin\Permission\Cores\AdminUserCustomerSubsystemMenu\AdminUserCustomerSubsystemMenuSync;
use Qz\Admin\Permission\Exceptions\MessageException;
use Qz\Admin\Permission\Facades\Access;
use Qz\Admin\Permission\Http\Controllers\Admin\AdminController;
use Qz\Admin\Permission\Models\AdminDepartment;
use Qz\Admin\Permission\Models\AdminMenu;
use Qz\Admin\Permission\Models\AdminRoleRequest;
use Qz\Admin\Permission\Models\AdminUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystem;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystemDepartment;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystemRequestDepartment;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystemRequestEmployee;

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
                Rule::exists('admin_user_customer_subsystems')
                    ->withoutTrashed()
            ],
            'permission' => [
                'required',
                'array'
            ],
            'data_permission' => [
                'required',
                'array'
            ]
        ], [
            'id.required' => '请选择员工',
            'id.exists' => '员工id不存在',
            'permission.required' => '请选择功能权限',
            'permission.array' => '功能权限格式有误',
            'data_permission.required' => '请选择数据权限',
            'data_permission.array' => '数据权限格式有误',
        ]);
        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }
        $featurePermission = GetFeaturePermissionByAdminUserCustomerSubsystemId::init()
            ->setAdminUserCustomerSubsystemId($this->getParam('id'))
            ->setFeaturePermission($this->getParam('permission'))
            ->run();
        $adminMenus = $featurePermission->getAdminMenus();
        $adminPageColumns = $featurePermission->getAdminPageColumns();
        $adminPageOptions = $featurePermission->getAdminPageOptions();

        $dataPermission = GetDataPermissionByAdminUserCustomerSubsystemId::init()
            ->setAdminUserCustomerSubsystemId($this->getParam('id'))
            ->setDataPermission($this->getParam('data_permission'))
            ->run();

        $adminUserCustomerSubsystemRequestDepartments = $dataPermission->getAdminUserCustomerSubsystemRequestDepartments();
        $adminUserCustomerSubsystemRequestEmployees = $dataPermission->getAdminUserCustomerSubsystemRequestEmployees();

        $id = AdminUserCustomerSubsystemUpdatePermission::init()
            ->setId($this->getParam('id'))
            ->setAdminMenu($adminMenus)
            ->setAdminPageColumn($adminPageColumns)
            ->setAdminPageOption($adminPageOptions)
            ->setAdminUserCustomerSubsystemRequestDepartments($adminUserCustomerSubsystemRequestDepartments)
            ->setAdminUserCustomerSubsystemRequestEmployees($adminUserCustomerSubsystemRequestEmployees)
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
                Rule::exists('admin_user_customer_subsystems')
                    ->withoutTrashed()
            ]
        ], [
            'id.required' => '请选择员工',
            'id.exists' => '员工id不存在',
        ]);
        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }

        if ($this->getParam('type') == 'data'){
            //数据权限
            $data = $this->getDataPermission($this->getParam('id'));
            return $this->success($data);
        }else{
            $menus = $this->getFeaturePermission($this->getParam('id'));
            return $this->success($menus);
        }

    }

    protected function getFeaturePermission($adminUserCustomerSubsystemId)
    {
        //获取用户的功能权限
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
                });
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
        return GetTreeAdminMenusWithCheck::init()
            ->setAdminMenus($data)
            ->setAdminMenuIds($permission->getAdminMenuIds())
            ->setAdminPageColumnIds($permission->getAdminPageColumnIds())
            ->setAdminPageOptionIds($permission->getAdminPageOptionIds())
            ->run()
            ->getTreeAdminMenus();
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

    protected function getDataPermission($adminUserCustomerSubsystemId){
        //获取用户所有的数据权限
        //1、根据用户id、用户角色、返回所有数据权限(admin_request_id)
        $data = [];
        $adminUserCustomerSubsystemRequestDepartments = AdminUserCustomerSubsystemRequestDepartment::query()
            ->where('admin_user_customer_subsystem_id', $adminUserCustomerSubsystemId)
            ->pluck('type', 'admin_request_id')
            ->toArray();

        //员工额外添加或删除的其余员工权限
        $adminUserCustomerSubsystemRequestEmployees = AdminUserCustomerSubsystemRequestEmployee::query()
            ->where('admin_user_customer_subsystem_id', $adminUserCustomerSubsystemId)
            ->select(['id', 'admin_request_id', 'type', 'permission_admin_user_customer_subsystem_id'])
            ->get()
            ->groupBy(['admin_request_id', 'type'])
            ->toArray();

        $adminUserRoleIds = GetInfoByAdminUserCustomerSubsystemId::init()
            ->setAdminUserCustomerSubsystemId($adminUserCustomerSubsystemId)
            ->getAdminUserCustomerSubsystemRoleIds();

        $adminRoleRequests = AdminRoleRequest::query()
            ->whereIn('admin_role_id', $adminUserRoleIds);
        if ($adminUserCustomerSubsystemRequestDepartments){
            $adminRoleRequests = $adminRoleRequests->whereNotIn('admin_request_id', array_keys($adminUserCustomerSubsystemRequestDepartments));
            foreach ($adminUserCustomerSubsystemRequestDepartments as $key => $adminUserCustomerSubsystemRequestDepartment){
                $type = $adminUserCustomerSubsystemRequestDepartment ? explode(AdminUserCustomerSubsystemRequestDepartment::CHARACTER, $adminUserCustomerSubsystemRequestDepartment) : [];
                $adminUserCustomerSubsystemRequestDepartments[$key] = $type;
            }
        }
        $adminRoleRequests = $adminRoleRequests
            ->orderByDesc('admin_request_id')
            ->select(['type', 'admin_request_id'])
            ->get()
            ->groupBy('admin_request_id')
            ->toArray();
        if ($adminRoleRequests){
            $adminRoleRequestGroups = [];
            foreach ($adminRoleRequests as $adminRequestId => $adminRoleRequest){
                $adminRoleRequestTypes = [];
                foreach ($adminRoleRequest as $item){
                    $type = Arr::get($item, 'type') ? explode(AdminRoleRequest::CHARACTER, Arr::get($item, 'type')) : [];
                    $adminRoleRequestTypes = array_merge($adminRoleRequestTypes, $type);
                }
                $adminRoleRequestGroups[$adminRequestId] = array_values(array_unique($adminRoleRequestTypes));
            }
            $adminRoleRequests = $adminRoleRequestGroups;
        }
        $adminRequests = $adminRoleRequests + $adminUserCustomerSubsystemRequestDepartments;

        if (empty($adminRequests)){
            //未设置权限
            $adminRequests = [[]];
        }

        $adminDepartments = [];
        $adminLoginUserRequestDepartmentAndUsers = [];
        //登录用户所有的数据权限
        if ($this->isAdministrator()){
            //超管所有部门、所有员工都可查看
            $adminDepartments = AdminDepartment::query()
                ->where('customer_subsystem_id', Access::getCustomerSubsystemId())
                ->orderBy('level')
                ->get()
                ->toArray();
            $adminDepartments = GetTreeDepartmentList::init()
                ->setAdminDepartments($adminDepartments)
                ->run()
                ->getTreeAdminDepartments();

            $adminLoginUserRequestDepartmentAndUsers = AdminUserCustomerSubsystemDepartment::query()
                ->whereHas('adminDepartment', function (Builder $builder){
                    $builder->where('customer_subsystem_id', Access::getCustomerSubsystemId());
                })
                //->whereIn('admin_user_customer_subsystem_id', Arr::pluck($adminLoginUserRequestEmoloyees, 'id'))
                ->select(['admin_department_id', 'admin_user_customer_subsystem_id'])
                ->get();
            if ($adminLoginUserRequestDepartmentAndUsers->isNotEmpty()){
                $adminLoginUserRequestDepartmentAndUsers = $adminLoginUserRequestDepartmentAndUsers->load([
                    'adminUserCustomerSubsystem:id,admin_user_id',
                    'adminUserCustomerSubsystem.adminUser:id,name'
                ]);
                $adminLoginUserRequestDepartmentAndUsers = $adminLoginUserRequestDepartmentAndUsers->groupBy('admin_department_id')
                    ->toArray();
            }
        }

        foreach ($adminRequests as $adminRequestId => $actions){
            $adminRequestEmployees = GetAdminUserCustomerSubsystemIdsByAdminUserCustomerSubsystemIdAndType::init()
                ->setAdminUserCustomerSubSystemId($adminUserCustomerSubsystemId)
                ->setDepartmentType($actions)
                ->run()
                ->getAdminUserCustomerSubSystemIds();

            if (Arr::get($adminUserCustomerSubsystemRequestEmployees, $adminRequestId)){
                Arr::get($adminUserCustomerSubsystemRequestEmployees, $adminRequestId. '.add') && $adminRequestEmployees = array_merge($adminRequestEmployees, Arr::pluck(Arr::get($adminUserCustomerSubsystemRequestEmployees, $adminRequestId. '.add'), 'permission_admin_user_customer_subsystem_id'));
                Arr::get($adminUserCustomerSubsystemRequestEmployees, $adminRequestId. '.delete') && $adminRequestEmployees = array_diff($adminRequestEmployees, Arr::pluck(Arr::get($adminUserCustomerSubsystemRequestEmployees, $adminRequestId. '.delete'), 'permission_admin_user_customer_subsystem_id'));
            }
            //登录员工能看到的权限列表
            if (empty($this->isAdministrator())){
                //登录员工当前可查看的部门
                $adminDepartmentIds = AdminUserCustomerSubsystemDepartment::query()
                    ->where('admin_user_customer_subsystem_id', Access::getAdminUserCustomerSubsystemId())
                    ->where('administrator', 1)
                    ->pluck('admin_department_id')
                    ->toArray();
                $adminDepartmentIds = GetSubAdminDepartmentIdsByAdminDepartmentIds::init()
                    ->setAdminDepartmentIds($adminDepartmentIds)
                    ->run()
                    ->getAllAdminDepartmentIds();
                $adminDepartments = AdminDepartment::query()
                    ->whereIn('id', $adminDepartmentIds)
                    ->orderBy('level')
                    ->get();
                $adminDepartments = $adminDepartments->toArray();

                $adminDepartments = GetTreeDepartmentList::init()
                    ->setAdminDepartments($adminDepartments)
                    ->run()
                    ->getTreeAdminDepartments();

                //$adminUserCustomerSubsystemIds = Arr::prepend($adminLoginUserRequestEmoloyees, Access::getAdminUserCustomerSubsystemId());
                $adminLoginUserRequestDepartmentAndUsers = AdminUserCustomerSubsystemDepartment::query()
                    ->whereIn('admin_department_id', $adminDepartmentIds)
                    ->select(['admin_department_id', 'admin_user_customer_subsystem_id'])
                    ->get();
                if ($adminLoginUserRequestDepartmentAndUsers->isNotEmpty()){
                    $adminLoginUserRequestDepartmentAndUsers = $adminLoginUserRequestDepartmentAndUsers->load([
                        'adminUserCustomerSubsystem:id,admin_user_id',
                        'adminUserCustomerSubsystem.adminUser:id,name'
                    ]);
                    $adminLoginUserRequestDepartmentAndUsers = $adminLoginUserRequestDepartmentAndUsers->groupBy('admin_department_id');

                }

                $adminLoginUserRequestDepartmentAndUsers = $adminLoginUserRequestDepartmentAndUsers->toArray();
            }

            $department = GetTreeCheckDepartmentWithAdminUserCustomerSubsystem::init()
                ->setAdminDepartments($adminDepartments)
                ->setAdminUserCustomerSubsystemDepartments($adminLoginUserRequestDepartmentAndUsers)
                ->setCheckAdminUserCustomerSubsystemIds($adminRequestEmployees);
            $department = $department->run()
                ->getTreeAdminDepartments();


            $data[] = [
                "adminRequestId" => $adminRequestId,
                "actions" => $actions,
                "department" => $department
            ];

        }
        return $data;
    }

    /**
     * 根据请求、操作获取所有员工列表（包括可勾选）
     * @return JsonResponse
     * @throws MessageException
     */
    public function departmentAdminUserPermission()
    {
        $validator = Validator::make($this->getParam(), [
            'id' => [
                'required',
                Rule::exists('admin_user_customer_subsystems')
                    ->withoutTrashed()
            ],
            'actions' => [
                'array'
            ],
            'admin_request_id' => [
                'required'
            ]
        ], [
            'id.required' => '请选择员工',
            'id.exists' => '员工id不存在',
            'actions.array' => '操作必须为数组',
            'admin_request_id.required' => '请选择请求id',
        ]);
        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }
        $adminRequestId = $this->getParam('admin_request_id');
        $actions = $this->getParam('actions');

        $adminLoginUserRequestEmoloyees = GetAdminUserIdsByAdminUserCustomerSubsystemId::init()
            ->setAdminUserCustomerSubSystemId(Access::getAdminUserCustomerSubsystemId())
            ->setAdminRequestId($adminRequestId)
            ->run()
            ->getAdminUserCustomerSubSystemIds();

        $adminDepartments = AdminDepartment::query();
        //登录员工当前可查看的部门
        if (empty($this->isAdministrator())){
            $adminDepartmentIds = AdminUserCustomerSubsystemDepartment::query()
                ->where('admin_user_customer_subsystem_id', Access::getAdminUserCustomerSubsystemId())
                ->where('administrator', 1)
                ->pluck('admin_department_id')
                ->toArray();
            $adminDepartmentIds = GetSubAdminDepartmentIdsByAdminDepartmentIds::init()
                ->setAdminDepartmentIds($adminDepartmentIds)
                ->run()
                ->getAllAdminDepartmentIds();
            $adminDepartments = $adminDepartments
                ->whereIn('id', $adminDepartmentIds);
        }
        $adminDepartments = $adminDepartments
            ->get()
            ->toArray();

        $adminDepartments = GetTreeDepartmentList::init()
            ->setAdminDepartments($adminDepartments)
            ->run()
            ->getTreeAdminDepartments();



        //员工可选择记录
        $adminRequestEmployees = GetAdminUserCustomerSubsystemIdsByAdminUserCustomerSubsystemIdAndType::init()
            ->setAdminUserCustomerSubSystemId($this->getParam('id'))
            ->setDepartmentType($actions)
            ->run()
            ->getAdminUserCustomerSubSystemIds();

        //$adminUserCustomerSubsystemIds = Arr::prepend($adminLoginUserRequestEmoloyees, Access::getAdminUserCustomerSubsystemId());
        $adminLoginUserRequestDepartmentAndUsers = AdminUserCustomerSubsystemDepartment::query();
        if (empty($this->isAdministrator())){
            $adminLoginUserRequestDepartmentAndUsers = $adminLoginUserRequestDepartmentAndUsers->whereIn('admin_department_id', $adminDepartmentIds);
        }
        $adminLoginUserRequestDepartmentAndUsers =  $adminLoginUserRequestDepartmentAndUsers
            ->select(['admin_department_id', 'admin_user_customer_subsystem_id'])
            ->get();
        if ($adminLoginUserRequestDepartmentAndUsers->isNotEmpty()){
            $adminLoginUserRequestDepartmentAndUsers = $adminLoginUserRequestDepartmentAndUsers->load([
                'adminUserCustomerSubsystem:id,admin_user_id',
                'adminUserCustomerSubsystem.adminUser:id,name'
            ]);
            $adminLoginUserRequestDepartmentAndUsers = $adminLoginUserRequestDepartmentAndUsers->groupBy('admin_department_id');

        }

        $adminLoginUserRequestDepartmentAndUsers = $adminLoginUserRequestDepartmentAndUsers->toArray();

        $department = GetTreeCheckDepartmentWithAdminUserCustomerSubsystem::init()
            ->setAdminDepartments($adminDepartments)
            ->setAdminUserCustomerSubsystemDepartments($adminLoginUserRequestDepartmentAndUsers)
            ->setCheckAdminUserCustomerSubsystemIds($adminRequestEmployees)
            ->run()
            ->getTreeAdminDepartments();

        $data = [
            "adminRequestId" => $adminRequestId,
            "actions" => $actions,
            "department" => $department
        ];

        return $this->success($data);


    }

    public function departmentPermission()
    {
        $validator = Validator::make($this->getParam(), [
            'id' => [
                'required',
                Rule::exists('admin_user_customer_subsystems')
                    ->withoutTrashed()
            ],
            'actions' => [
                'array'
            ]
        ], [
            'id.required' => '请选择员工',
            'id.exists' => '员工id不存在',
            'actions.array' => '操作必须为数组',
        ]);
        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }
        if (empty($this->getParam('actions'))){
            return $this->success([]);
        }
        $adminUserCustomerSubsystemIds = GetAdminUserCustomerSubsystemIdsByAdminUserCustomerSubsystemIdAndType::init()
            ->setAdminUserCustomerSubSystemId($this->getParam('id'))
            ->setDepartmentType($this->getParam('actions'))
            ->run()
            ->getAdminUserCustomerSubSystemIds();
        if (empty($adminUserCustomerSubsystemIds)){
            return $this->success([]);
        }
        $data = [];
        $adminUserCustomerDepartments = AdminUserCustomerSubsystemDepartment::query()
            ->whereIn('admin_user_customer_subsystem_id', $adminUserCustomerSubsystemIds)
            ->select(['admin_department_id','admin_user_customer_subsystem_id'])
            ->get()
            ->toArray();
        foreach ($adminUserCustomerDepartments as $adminUserCustomerDepartment){
            $data[] = "department_" . Arr::get($adminUserCustomerDepartment, 'admin_department_id'). '-' . Arr::get($adminUserCustomerDepartment, 'admin_user_customer_subsystem_id');
        }
        return $this->success($data);
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
