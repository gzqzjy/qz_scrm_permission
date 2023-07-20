<?php

namespace Qz\Admin\Permission\Http\Controllers\Admin\AdminUser;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Qz\Admin\Permission\Cores\AdminDepartment\GetTreeCheckDepartmentWithAdminUser;
use Qz\Admin\Permission\Cores\AdminDepartment\GetTreeDepartmentList;
use Qz\Admin\Permission\Cores\AdminMenu\GetTreeAdminMenusWithCheck;
use Qz\Admin\Permission\Cores\AdminUser\AdminUserAdd;
use Qz\Admin\Permission\Cores\AdminUser\AdminUserDelete;
use Qz\Admin\Permission\Cores\AdminUser\AdminUserUpdate;
use Qz\Admin\Permission\Cores\AdminUser\AdminUserUpdatePermission;
use Qz\Admin\Permission\Cores\AdminUser\GetAdminUserIdsByAdminUserIdAndType;
use Qz\Admin\Permission\Cores\AdminUser\GetAdminUserIdsByAdminUserId;
use Qz\Admin\Permission\Cores\AdminUser\GetDataPermissionByAdminUserId;
use Qz\Admin\Permission\Cores\AdminUser\GetFeaturePermissionByAdminUserId;
use Qz\Admin\Permission\Cores\AdminUser\GetPermissionByAdminUserId;
use Qz\Admin\Permission\Cores\AdminUser\GetSubAdminDepartmentIdsByAdminDepartmentIds;
use Qz\Admin\Permission\Cores\AdminUserMenu\AdminUserMenuSync;
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
use Qz\Admin\Permission\Models\AdminUserDepartment;
use Qz\Admin\Permission\Models\AdminUserRequest;
use Qz\Admin\Permission\Models\AdminUserRequestEmployee;
use Qz\Admin\Permission\Models\AdminUserRole;

class AdminUserController extends AdminController
{
    public function get()
    {
        $model = AdminUser::query();
        $model = $this->filter($model);
        if ($this->getParam('admin_department_id')) {
            $model = $model->whereHas('adminUsers', function (Builder $builder) {
                $builder->whereHas('adminUserDepartments', function (Builder $builder) {
                    $builder->where('admin_department_id', $this->getParam('admin_department_id'));
                });
            });
        }
        $model = $model->get();
        $model->load([
            'adminUsers',
            'adminUsers.adminUserDepartments',
            'adminUsers.adminUserRoles'
        ]);
        $model->append(['statusDesc']);
        foreach ($model as &$item) {
            $adminDepartments = Arr::get($item, 'adminUsers.0.adminUserDepartments');
            $adminRoles = Arr::get($item, 'adminUsers.0.adminUserRoles');
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
        $this->addParam('customer_id', Access::getCustomerId());
        $validator = Validator::make($this->getParam(), [
            'mobile' => [
                Rule::unique(AdminUser::class)
                    ->withoutTrashed(),
            ],
        ], [
            'mobile.unique' => '员工手机号不能重复',
        ]);
        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }
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
                Rule::exists(AdminUser::class)
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
                Rule::exists(AdminUser::class)
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
        $featurePermission = GetFeaturePermissionByAdminUserId::init()
            ->setAdminUserId($this->getParam('id'))
            ->setFeaturePermission($this->getParam('permission'))
            ->run();
        $adminMenus = $featurePermission->getAdminMenus();
        $adminPageColumns = $featurePermission->getAdminPageColumns();
        $adminPageOptions = $featurePermission->getAdminPageOptions();

        $dataPermission = GetDataPermissionByAdminUserId::init()
            ->setAdminUserId($this->getParam('id'))
            ->setDataPermission($this->getParam('data_permission'))
            ->run();

        $adminUserRequestDepartments = $dataPermission->getAdminUserRequests();
        $adminUserRequestEmployees = $dataPermission->getAdminUserRequestEmployees();
        $id = AdminUserUpdatePermission::init()
            ->setId($this->getParam('id'))
            ->setAdminMenu($adminMenus)
            ->setAdminPageColumn($adminPageColumns)
            ->setAdminPageOption($adminPageOptions)
            ->setAdminUserRequests($adminUserRequestDepartments)
            ->setAdminUserRequestEmployees($adminUserRequestEmployees)
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
            ->selectRaw($select);
        $model = $this->filter($model);
        $model = $model->get();
        return $this->json($model);
    }

    public function allStatus()
    {
        $statusDesc = AdminUser::STATUS_DESC;
        $data = [];
        foreach ($statusDesc as $value => $label) {
            $data[] = compact('value', 'label');
        }
        return $this->json($data);
    }

    public function allSex()
    {
        $statusDesc = AdminUser::SEX_DESC;
        Arr::forget($statusDesc, [AdminUser::SEX_UNKNOWN]);
        $data = [];
        foreach ($statusDesc as $value => $label) {
            $data[] = compact('value', 'label');
        }
        return $this->json($data);
    }


    public function permission()
    {
        $validator = Validator::make($this->getParam(), [
            'id' => [
                'required',
                Rule::exists(AdminMenu::class)
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

    protected function getFeaturePermission($adminUserId)
    {
        //获取用户的功能权限
        $model = AdminMenu::query();
        $administrator = $this->isAdministrator();
        $adminMenuIds = $adminPageOptionIds = $adminPageColumnIds = [];
        if (empty($administrator)) {
            //获取用户的角色
            $permission = GetPermissionByAdminUserId::init()
                ->setAdminUserId(Access::getAdminUserId())
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
        $permission = GetPermissionByAdminUserId::init()
            ->setAdminUserId($adminUserId)
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

    protected function getDataPermission($adminUserId){
        //获取用户所有的数据权限
        //1、根据用户id、用户角色、返回所有数据权限(admin_request_id)
        $data = [];
        $adminUserRequestDepartments = AdminUserRequest::query()
            ->where('admin_user_id', $adminUserId)
            ->pluck('type', 'admin_request_id')
            ->toArray();

        //员工额外添加或删除的其余员工权限
        $adminUserRequestEmployees = AdminUserRequestEmployee::query()
            ->where('admin_user_id', $adminUserId)
            ->select(['id', 'admin_request_id', 'type', 'permission_admin_user_id'])
            ->get()
            ->groupBy(['admin_request_id', 'type'])
            ->toArray();

        $adminUserRoleIds = AdminUserRole::query()
            ->where('admin_user_id', $adminUserId)
            ->pluck('admin_role_id');

        $adminRoleRequests = AdminRoleRequest::query()
            ->whereIn('admin_role_id', $adminUserRoleIds);
        if ($adminUserRequestDepartments){
            $adminRoleRequests = $adminRoleRequests->whereNotIn('admin_request_id', array_keys($adminUserRequestDepartments));
            foreach ($adminUserRequestDepartments as $key => $adminUserRequestDepartment){
                $type = $adminUserRequestDepartment ? explode(AdminUserRequest::CHARACTER, $adminUserRequestDepartment) : [];
                $adminUserRequestDepartments[$key] = $type;
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
        $adminRequests = $adminRoleRequests + $adminUserRequestDepartments;

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
                ->orderBy('level')
                ->get()
                ->toArray();
            $adminDepartments = GetTreeDepartmentList::init()
                ->setAdminDepartments($adminDepartments)
                ->run()
                ->getTreeAdminDepartments();

            $adminLoginUserRequestDepartmentAndUsers = AdminUserDepartment::query()
                ->whereHas('adminDepartment', function (Builder $builder){
                    $builder->where('customer_id', $this->getCustomerId());
                })
                ->select(['admin_department_id', 'admin_user_id'])
                ->get();
            if ($adminLoginUserRequestDepartmentAndUsers->isNotEmpty()){
                $adminLoginUserRequestDepartmentAndUsers = $adminLoginUserRequestDepartmentAndUsers->load([
                    'adminUser:id,name',
                ]);
                $adminLoginUserRequestDepartmentAndUsers = $adminLoginUserRequestDepartmentAndUsers->groupBy('admin_department_id')
                    ->toArray();
            }
        }

        foreach ($adminRequests as $adminRequestId => $actions){
            $adminRequestEmployees = GetAdminUserIdsByAdminUserIdAndType::init()
                ->setAdminUserCustomerSubSystemId($adminUserId)
                ->setDepartmentType($actions)
                ->run()
                ->getAdminUserCustomerSubSystemIds();

            if (Arr::get($adminUserRequestEmployees, $adminRequestId)){
                Arr::get($adminUserRequestEmployees, $adminRequestId. '.add') && $adminRequestEmployees = array_merge($adminRequestEmployees, Arr::pluck(Arr::get($adminUserRequestEmployees, $adminRequestId. '.add'), 'permission_admin_user_id'));
                Arr::get($adminUserRequestEmployees, $adminRequestId. '.delete') && $adminRequestEmployees = array_diff($adminRequestEmployees, Arr::pluck(Arr::get($adminUserRequestEmployees, $adminRequestId. '.delete'), 'permission_admin_user_id'));
            }
            //登录员工能看到的权限列表
            if (empty($this->isAdministrator())){
                //登录员工当前可查看的部门
                $adminDepartmentIds = AdminUserDepartment::query()
                    ->where('admin_user_id', Access::getAdminUserId())
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

                //$adminUserIds = Arr::prepend($adminLoginUserRequestEmoloyees, Access::getAdminUserId());
                $adminLoginUserRequestDepartmentAndUsers = AdminUserDepartment::query()
                    ->whereIn('admin_department_id', $adminDepartmentIds)
                    ->select(['admin_department_id', 'admin_user_id'])
                    ->get();
                if ($adminLoginUserRequestDepartmentAndUsers->isNotEmpty()){
                    $adminLoginUserRequestDepartmentAndUsers = $adminLoginUserRequestDepartmentAndUsers->load([
                        'adminUser:id,name',
                    ]);
                    $adminLoginUserRequestDepartmentAndUsers = $adminLoginUserRequestDepartmentAndUsers->groupBy('admin_department_id');
                }

                $adminLoginUserRequestDepartmentAndUsers = $adminLoginUserRequestDepartmentAndUsers->toArray();
            }

            $department = GetTreeCheckDepartmentWithAdminUser::init()
                ->setAdminDepartments($adminDepartments)
                ->setAdminUserDepartments($adminLoginUserRequestDepartmentAndUsers)
                ->setCheckAdminUserIds($adminRequestEmployees);
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
                Rule::exists(AdminUser::class)
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

        $adminLoginUserRequestEmoloyees = GetAdminUserIdsByAdminUserId::init()
            ->setAdminUserCustomerSubSystemId(Access::getAdminUserId())
            ->setAdminRequestId($adminRequestId)
            ->run()
            ->getAdminUserCustomerSubSystemIds();

        $adminDepartments = AdminDepartment::query();
        //登录员工当前可查看的部门
        if (empty($this->isAdministrator())){
            $adminDepartmentIds = AdminUserDepartment::query()
                ->where('admin_user_id', Access::getAdminUserId())
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
        $adminRequestEmployees = GetAdminUserIdsByAdminUserIdAndType::init()
            ->setAdminUserCustomerSubSystemId($this->getParam('id'))
            ->setDepartmentType($actions)
            ->run()
            ->getAdminUserCustomerSubSystemIds();

        //$adminUserIds = Arr::prepend($adminLoginUserRequestEmoloyees, Access::getAdminUserId());
        $adminLoginUserRequestDepartmentAndUsers = AdminUserDepartment::query();
        if (empty($this->isAdministrator())){
            $adminLoginUserRequestDepartmentAndUsers = $adminLoginUserRequestDepartmentAndUsers->whereIn('admin_department_id', $adminDepartmentIds);
        }
        $adminLoginUserRequestDepartmentAndUsers =  $adminLoginUserRequestDepartmentAndUsers
            ->select(['admin_department_id', 'admin_user_id'])
            ->get();
        if ($adminLoginUserRequestDepartmentAndUsers->isNotEmpty()){
            $adminLoginUserRequestDepartmentAndUsers = $adminLoginUserRequestDepartmentAndUsers->load([
                'adminUser:id,name',
            ]);
            $adminLoginUserRequestDepartmentAndUsers = $adminLoginUserRequestDepartmentAndUsers->groupBy('admin_department_id');

        }

        $adminLoginUserRequestDepartmentAndUsers = $adminLoginUserRequestDepartmentAndUsers->toArray();

        $department = GetTreeCheckDepartmentWithAdminUser::init()
            ->setAdminDepartments($adminDepartments)
            ->setAdminUserDepartments($adminLoginUserRequestDepartmentAndUsers)
            ->setCheckAdminUserIds($adminRequestEmployees)
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
                Rule::exists(AdminUser::class)
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
        $adminUserIds = GetAdminUserIdsByAdminUserIdAndType::init()
            ->setAdminUserCustomerSubSystemId($this->getParam('id'))
            ->setDepartmentType($this->getParam('actions'))
            ->run()
            ->getAdminUserCustomerSubSystemIds();
        if (empty($adminUserIds)){
            return $this->success([]);
        }
        $data = [];
        $adminUserCustomerDepartments = AdminUserDepartment::query()
            ->whereIn('admin_user_id', $adminUserIds)
            ->select(['admin_department_id','admin_user_id'])
            ->get()
            ->toArray();
        foreach ($adminUserCustomerDepartments as $adminUserCustomerDepartment){
            $data[] = "department_" . Arr::get($adminUserCustomerDepartment, 'admin_department_id'). '-' . Arr::get($adminUserCustomerDepartment, 'admin_user_id');
        }
        return $this->success($data);
    }


    public function addMenus()
    {
        $id = $this->getParam('id');
        $adminUser = AdminUser::query()
            ->where('admin_user_id', $id)
            ->first();
        if (empty($adminUser)) {
            return $this->success();
        }
        $menuIds = Arr::collapse($this->getParam('menu_ids'));
        $menuIds = array_unique($menuIds);
        AdminUserMenuSync::init()
            ->setAdminUserId(Arr::get($adminUser, 'id'))
            ->setAdminMenuIds($menuIds)
            ->run();
        return $this->success();
    }

}
