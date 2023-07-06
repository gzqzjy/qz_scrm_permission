<?php

namespace Qz\Admin\Permission\Http\Controllers\Admin\AdminDepartment;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Qz\Admin\Permission\Cores\AdminDepartment\AdminDepartmentAdd;
use Qz\Admin\Permission\Cores\AdminDepartment\AdminDepartmentDelete;
use Qz\Admin\Permission\Cores\AdminDepartment\AdminDepartmentUpdate;
use Qz\Admin\Permission\Cores\AdminDepartment\GetTreeDepartmentList;
use Qz\Admin\Permission\Cores\AdminUserCustomerSubsystem\GetSubAdminDepartmentIdsByAdminDepartmentIds;
use Qz\Admin\Permission\Cores\Common\Filter;
use Qz\Admin\Permission\Exceptions\MessageException;
use Qz\Admin\Permission\Facades\Access;
use Qz\Admin\Permission\Http\Controllers\Admin\AdminController;
use Qz\Admin\Permission\Models\AdminCategoryDepartment;
use Qz\Admin\Permission\Models\AdminDepartment;
use Qz\Admin\Permission\Models\AdminDepartmentRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Qz\Admin\Permission\Models\AdminRole;
use Qz\Admin\Permission\Models\AdminUser;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystemDepartment;
use Qz\Admin\Permission\Models\Category;

class AdminDepartmentController extends AdminController
{
    public function get()
    {
        $model = AdminDepartment::query()
            ->where('customer_subsystem_id', Access::getCustomerSubsystemId());
        $administrator = $this->isAdministrator();
        if (empty($administrator)) {
            //获取用户所有可管理的部门
            $adminDepartmentIds = AdminUserCustomerSubsystemDepartment::query()
                ->where('admin_user_customer_subsystem_id', Access::getAdminUserCustomerSubsystemId())
                ->where('administrator', 1)
                ->pluck('admin_department_id')
                ->toArray();
            if (empty($adminDepartmentIds)) {
                return $this->success([]);
            }
            $adminDepartmentIds = GetSubAdminDepartmentIdsByAdminDepartmentIds::init()
                ->setAdminDepartmentIds($adminDepartmentIds)
                ->run()
                ->getAllAdminDepartmentIds();
            $model->whereIn('id', $adminDepartmentIds);
        }
        $model = $this->filter($model);
        $filter = [];
        if ($this->getParam('filter')){
            $filter = $this->getChildFilter();
        }
        $model = $model
            ->orderBy('level')
            ->get();
        $model->load([
            'adminUserCustomerSubsystemDepartments.adminUserCustomerSubsystem.adminUser' => function(BelongsTo $belongsTo) use ($filter){
                if ($adminUser = Arr::get($filter, 'adminUserCustomerSubsystemDepartments.adminUserCustomerSubsystem.adminUser')){
                    $belongsTo = Filter::init()
                        ->setModel($belongsTo)
                        ->setParam($adminUser)
                        ->run()
                        ->getModel();
                }
            },
            'adminUserCustomerSubsystemDepartments.adminUserCustomerSubsystem.adminUserCustomerSubsystemRoles.adminRole',
            'adminUserCustomerSubsystemDepartments.adminUserCustomerSubsystem.adminUserCustomerSubsystemDepartments',
            'adminCategoryDepartments',
            'adminDepartmentRoles',
        ]);
        $model->loadCount([
            'adminDepartmentRoles',
            'adminCategoryDepartments',
            'adminUserCustomerSubsystemDepartments'
        ]);
        $model = $model->toArray();
        $data = [];
        $existDepartmentIds = [];
        foreach ($model as $value) {
            if ($item = $this->item($value, $model, $existDepartmentIds, Arr::get($value, 'id'))) {
                $data[] = $item;
            }
        }
        return $this->success($data);
    }

    protected function item($value, $array, &$existDepartmentIds, $pid = 0)
    {
        if (in_array(Arr::get($value, 'id'), $existDepartmentIds)) {
            return [];
        }
        $existDepartmentIds[] = Arr::get($value, 'id');
        $data = Arr::except($value, ['admin_user_customer_subsystem_departments', 'admin_category_departments', 'admin_department_roles']);
        $data['category_ids'] = Arr::pluck(Arr::get($value, 'admin_category_departments'), 'category_id');
        $data['admin_role_ids'] = Arr::pluck(Arr::get($value, 'admin_department_roles'), 'admin_role_id');
        if (Arr::get($value, 'admin_user_customer_subsystem_departments')) {
            $adminUsers = [];
            $statusDesc = AdminUser::STATUS_DESC;
            foreach (Arr::get($value, 'admin_user_customer_subsystem_departments') as $item) {
                if (empty(Arr::get($item, 'admin_user_customer_subsystem.admin_user'))){
                    continue;
                }
                $roles = Arr::get($item, 'admin_user_customer_subsystem.admin_user_customer_subsystem_roles');
                $roleName = Arr::pluck(Arr::pluck($roles, 'admin_role'), 'name');
                $roleId = Arr::pluck($roles, 'admin_role_id');
                $adminDepartments = Arr::get($item, 'admin_user_customer_subsystem.admin_user_customer_subsystem_departments');
                $adminDepartments = array_map(function ($value) {
                    return [
                        'id' => Arr::get($value, 'admin_department_id'),
                        'administrator' => Arr::get($value, 'administrator')
                    ];
                }, $adminDepartments);

                Arr::set($item, 'admin_user_customer_subsystem.admin_user.adminDepartments', $adminDepartments);
                Arr::set($item, 'admin_user_customer_subsystem.admin_user.adminRoleNames', implode(",", $roleName));
                Arr::set($item, 'admin_user_customer_subsystem.admin_user.adminRoleIds', $roleId);
                Arr::set($item, 'admin_user_customer_subsystem.admin_user.statusDesc', $statusDesc[Arr::get($item, 'admin_user_customer_subsystem.admin_user.status')]);
                $adminDepartmentAdministrators = array_column($adminDepartments, null, 'id');
                Arr::set($item, 'admin_user_customer_subsystem.admin_user.administrator', Arr::get($adminDepartmentAdministrators, Arr::get($item, 'admin_department_id') . '.administrator'));
                $adminUsers[] = Arr::get($item, 'admin_user_customer_subsystem.admin_user');
            }
            $data['admin_users'] = $adminUsers;
        }
        $children = [];
        foreach ($array as $item) {
            if (Arr::get($item, 'pid') == $pid) {
                if ($child = $this->item($item, $array, $existDepartmentIds, Arr::get($item, 'id'))) {
                    $children[] = $child;
                }
            }
        }
        $data['children'] = $children;
        return $data;
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
                Rule::unique(AdminDepartment::class)
                    ->where('customer_subsystem_id', Access::getCustomerSubsystemId())
                    ->withoutTrashed(),
            ],
            'pid' => [
                'sometimes',
                'required',
                Rule::exists('admin_departments', 'id')
                    ->withoutTrashed(),
            ],
            'categoryIds' => ['array'],
            'adminRoleIds' => ['array'],
        ], [
            'name.required' => '部门名称不能为空',
            'name.unique' => '部门名称不能重复',
            'pid.exists' => '上级部门不存在',
            'categoryIds.array' => '品类格式有误',
            'adminRoleIds.array' => '角色格式有误',
        ]);
        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }
        if ($this->getParam('pid')) {
            $adminDepartment = AdminDepartment::query()
                ->find($this->getParam('pid'));
            $this->addParam('level', Arr::get($adminDepartment, 'level') + 1);
        } else {
            $this->addParam('pid', 0);
            $this->addParam('level', 1);
        }
        $this->addParam('customer_subsystem_id', Access::getCustomerSubsystemId());
        $id = AdminDepartmentAdd::init()
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
                Rule::exists('admin_departments', 'id')
                    ->withoutTrashed(),
            ],
            'name' => [
                'required',
                Rule::unique(AdminDepartment::class)
                    ->where('customer_subsystem_id', Access::getCustomerSubsystemId())
                    ->ignore($this->getParam('id'))
                    ->withoutTrashed(),
            ],
            'pid' => [
                'sometimes',
                'required',
                Rule::exists('admin_departments', 'id')
                    ->withoutTrashed(),
            ],
            'categoryIds' => [
                'array'
            ],
            'adminRoleIds' => [
                'array'
            ],
        ], [
            'id.required' => '部门id不能为空',
            'id.exists' => '部门不存在',
            'name.required' => '部门名称不能为空',
            'name.unique' => '部门名称不能重复',
            'pid.exists' => '上级部门不存在',
            'categoryIds.array' => '品类格式有误',
            'adminRoleIds.array' => '角色格式有误',
        ]);
        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }
        if ($this->getParam('pid')) {
            $adminDepartment = AdminDepartment::query()
                ->find($this->getParam('pid'));
            $this->addParam('level', Arr::get($adminDepartment, 'level') + 1);
        } else {
            $this->addParam('pid', 0);
            $this->addParam('level', 1);
        }
        $id = AdminDepartmentUpdate::init()
            ->setId($this->getParam('id'))
            ->setParam($this->getParam())
            ->run()
            ->getId();
        return $this->success(compact('id'));
    }

    public function destroy()
    {
        $id = $this->getParam('id');
        $isExist = AdminUserCustomerSubsystemDepartment::query()
            ->whereIn('admin_department_id', $id)
            ->exists();
        if ($isExist) {
            throw new MessageException("部门下有员工，不可删除！");
        }
        if (is_array($id)) {
            foreach ($id as $value) {
                AdminDepartmentDelete::init()
                    ->setId($value)
                    ->run()
                    ->getId();
            }
            return $this->success();
        }
        AdminDepartmentDelete::init()
            ->setId($id)
            ->run()
            ->getId();
        return $this->success();
    }

    public function all()
    {
        $param = $this->getParam();
        // $select = Arr::get($param, 'select', 'id as value, name as label');
        $model = AdminDepartment::query()
            ->where('customer_subsystem_id', Access::getCustomerSubsystemId());
        $model = $this->filter($model);
        $adminDepartmentIds = [];
        $data = [];
        $administrator = $this->isAdministrator();
        if (empty($administrator)) {
            $adminDepartmentModel = AdminUserCustomerSubsystemDepartment::query()
                ->where('admin_user_customer_subsystem_id', Access::getAdminUserCustomerSubsystemId());
            if ($this->getParam('administrator')) {
                //获取用户所有可管理的部门
                $adminDepartmentModel = $adminDepartmentModel->where('administrator', 1);
            }
            $adminDepartmentIds = $adminDepartmentModel
                ->pluck('admin_department_id')
                ->toArray();
            if (empty($adminDepartmentIds)) {
                return $this->response($data);
            }
            $adminDepartmentIds = GetSubAdminDepartmentIdsByAdminDepartmentIds::init()
                ->setAdminDepartmentIds($adminDepartmentIds)
                ->run()
                ->getAllAdminDepartmentIds();
            $model->whereIn('id', $adminDepartmentIds);
        }
        $model = $model
            ->orderBy('level')
            ->get();
        if ($model->isEmpty()){
            return $this->response($data);
        }
        $model = $model->toArray();

        $data = GetTreeDepartmentList::init()
            ->setAdminDepartments($model)
            ->run()
            ->getTreeAdminDepartments();
        return $this->response($data);
    }


    public function allDepartment()
    {
        $param = $this->getParam();
        // $select = Arr::get($param, 'select', 'id as value, name as label');
        $model = AdminDepartment::query()
            ->where('customer_subsystem_id', Access::getCustomerSubsystemId());
        $model = $this->filter($model);
        $adminDepartmentIds = [];
        $administrator = $this->isAdministrator();
        $adminDepartmentRoleModel = AdminDepartmentRole::query();
        $adminDepartmentUserModel = AdminUserCustomerSubsystemDepartment::query();
        $adminCategoryDepartmentModel = AdminCategoryDepartment::query();

        if (empty($administrator)) {
            $adminDepartmentIds = AdminUserCustomerSubsystemDepartment::query()
                ->where('admin_user_customer_subsystem_id', Access::getAdminUserCustomerSubsystemId())
                ->where('administrator', 1)
                ->pluck('admin_department_id')
                ->toArray();
            if (empty($adminDepartmentIds)) {
                return $this->success([]);
            }
            $model->whereIn('id', $adminDepartmentIds);
            $adminDepartmentRoleModel->whereIn('admin_department_id', $adminDepartmentIds);
            $adminDepartmentUserModel->whereIn('admin_department_id', $adminDepartmentIds);
            $adminCategoryDepartmentModel->whereIn('admin_department_id', $adminDepartmentIds);
        } else {
            $model->where('pid', 0);
        }

        $adminDepartmentRoles = $adminDepartmentRoleModel->get()
            ->groupBy('admin_department_id')
            ->toArray();

        $adminDepartmentRoleIds = $adminDepartmentRoleModel
            ->groupBy('admin_role_id')
            ->pluck('admin_role_id')
            ->toArray();

        $adminCategoryDepartments = $adminCategoryDepartmentModel->get()
            ->groupBy('admin_department_id')
            ->toArray();

        $adminCategoryDepartmentIds = $adminCategoryDepartmentModel
            ->groupBy('category_id')
            ->pluck('category_id')
            ->toArray();

        $adminUserCustomerSubsystems = $adminDepartmentUserModel->get()
            ->groupBy('admin_department_id')
            ->toArray();

        $adminUserCustomerSubsystemIds = $adminDepartmentUserModel
            ->pluck('admin_user_id', 'admin_user_customer_subsystem_id')
            ->toArray();

        $adminRoles = AdminRole::query()
            ->whereIn('id', $adminDepartmentRoleIds)
            ->pluck('name', 'id')
            ->toArray();

        $adminCategories = Category::query()
            ->whereIn('id', $adminCategoryDepartmentIds)
            ->pluck('name', 'id')
            ->toArray();
        $adminUsers = AdminUser::query()
            ->whereIn('id', array_values($adminUserCustomerSubsystemIds))
            ->get()
            ->groupBy('id')
            ->toArray();
        $model = $model
            ->get();
        $model->load([
            'children'
        ]);
        $model = $model->toArray();
        $data = [];
        $existDepartmentIds = [];
        foreach ($model as $value) {
            if ($item = $this->allDepartmentItem($value, $adminDepartmentIds, $adminRoles, $adminDepartmentRoles, $adminUserCustomerSubsystems, $adminUserCustomerSubsystemIds, $adminUsers, $adminCategoryDepartments, $adminCategoryDepartmentIds, $existDepartmentIds)) {
                $data[] = $item;
            }
        }
        return $this->response($data);
    }

    protected function allDepartmentItem($value, $adminDepartmentIds, $adminRoles, $adminDepartmentRoles, $adminUserCustomerSubsystems, $adminUserCustomerSubsystemIds, $adminUsers, $adminCategoryDepartments, $adminCategoryDepartmentIds, &$existDepartmentIds)
    {
        if (empty($this->isAdministrator()) && (!in_array(Arr::get($value, 'id'), $adminDepartmentIds) || in_array(Arr::get($value, 'id'), $existDepartmentIds))) {
            return [];
        }
        $existDepartmentIds[] = Arr::get($value, 'id');
        $data = Arr::except($value, 'children');
        //部门角色
        $data['admin_role_ids'] = Arr::get($adminDepartmentRoles, Arr::get($value, 'id')) ? Arr::pluck(Arr::get($adminDepartmentRoles, Arr::get($value, 'id')), 'id') : [];
        //部门品类
        $data['category_ids'] = Arr::get($adminCategoryDepartments, Arr::get($value, 'id')) ? Arr::pluck(Arr::get($adminCategoryDepartments, Arr::get($value, 'id')), 'id') : [];
        //部门员工
        $adminUserCustomerSubsystem = Arr::get($adminUserCustomerSubsystems, Arr::get($value, 'id')) ? Arr::pluck(Arr::get($adminUserCustomerSubsystems, Arr::get($value, 'id')), 'id') : [];

        $data['adminDepartmentRolesCount'] = count($data['admin_role_ids']);
        $data['adminCategoryDepartmentsCount'] = count($data['category_ids']);
        $data['adminUserCustomerSubsystemDepartmentsCount'] = count($adminUserCustomerSubsystem);

        if (Arr::get($value, 'children')) {
            $routes = [];
            $children = Arr::get($value, 'children');
            foreach ($children as $child) {
                if ($item = $this->allDepartmentItem($value, $adminDepartmentIds, $adminRoles, $adminDepartmentRoles, $adminUserCustomerSubsystems, $adminUserCustomerSubsystemIds, $adminUsers, $adminCategoryDepartments, $adminCategoryDepartmentIds, $existDepartmentIds)) {
                    $routes[] = $item;
                }
            }
            if (!empty($routes)) {
                Arr::set($data, 'children', $routes);
            }
        }
        return $data;
    }

}
