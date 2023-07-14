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
use Qz\Admin\Permission\Cores\AdminUser\GetSubAdminDepartmentIdsByAdminDepartmentIds;
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
use Qz\Admin\Permission\Models\AdminUserDepartment;
use Qz\Admin\Permission\Models\Category;

class AdminDepartmentController extends AdminController
{
    public function get()
    {
        $model = AdminDepartment::query();
        if (!$this->isAdministrator()) {
            $model->whereHas('adminUserDepartments', function (Builder $builder) {
                $builder->where('admin_user_id', $this->getLoginAdminUserId());
            });
        }
        $model = $this->filter($model);
        $filter = [];
        if ($this->getParam('filter')) {
            $filter = $this->getChildFilter();
        }
        $model = $model
            ->whereDoesntHave('parent', function (Builder $builder) {
                if (!$this->isAdministrator()) {
                    $builder->where('admin_user_id', $this->getLoginAdminUserId());
                }
            })
            ->orderBy('level')
            ->get();
        $model->load([
            'children',
            'adminUserDepartments',
            'adminUserDepartments.adminUser',
            'adminUserDepartments.adminUser.adminUserRoles',
            'adminUserDepartments.adminUser.adminUserRoles.adminRole',
            'adminUserDepartments.adminUser.adminUserDepartments',
            'adminCategoryDepartments',
            'adminDepartmentRoles',
        ]);
        $model->loadCount([
            'adminDepartmentRoles',
            'adminCategoryDepartments',
            'adminUserDepartments'
        ]);
        $model = $this->format($model);
        return $this->success($model->toArray());
    }

    protected function format($model)
    {
        foreach ($model as $value) {
            if (!$value->adminUserDepartments->isEmpty() || !$value->children->isEmpty()) {
                $value->deleteDisabled = true;
            }
            if (!$value->adminUserDepartments->isEmpty()) {
                foreach ($value->adminUserDepartments as $adminUserDepartment) {
                    $adminUserDepartment->adminUser->adminRoleIds = $adminUserDepartment->adminUser->adminUserRoles->pluck('admin_role_id');
                }
            }
            $value->adminRoleIds = $value->adminDepartmentRoles->pluck('admin_role_id');
            $value->categoryIds = $value->adminCategoryDepartments->pluck('category_id');
            if (!$value->children->isEmpty()) {
                $value->children = $this->format($value->children);
            }
        }
        return $model;
    }

    protected function item($value, $array, &$existDepartmentIds, $pid = 0)
    {
        if (in_array(Arr::get($value, 'id'), $existDepartmentIds)) {
            return [];
        }
        $existDepartmentIds[] = Arr::get($value, 'id');
        $value->category_ids = $value->adminCategoryDepartments->pluck('category_id');
        $value->admin_role_ids = $value->adminDepartmentRoles->pluck('admin_role_id');
        return $value;
//        $data = Arr::except($value, ['admin_user_departments', 'admin_category_departments', 'admin_department_roles']);
//        $data['category_ids'] = Arr::pluck(Arr::get($value, 'admin_category_departments'), 'category_id');
//        $data['admin_role_ids'] = Arr::pluck(Arr::get($value, 'admin_department_roles'), 'admin_role_id');
        if (Arr::get($value, 'admin_user_departments')) {
            $adminUsers = [];
            $statusDesc = AdminUser::STATUS_DESC;
            foreach (Arr::get($value, 'admin_user_departments') as $item) {
                if (empty(Arr::get($item, 'admin_user.admin_user'))) {
                    continue;
                }
                $roles = Arr::get($item, 'admin_user.admin_user_roles');
                $roleName = Arr::pluck(Arr::pluck($roles, 'admin_role'), 'name');
                $roleId = Arr::pluck($roles, 'admin_role_id');
                $adminDepartments = Arr::get($item, 'admin_user.admin_user_departments');
                $adminDepartments = array_map(function ($value) {
                    return [
                        'id' => Arr::get($value, 'admin_department_id'),
                        'administrator' => Arr::get($value, 'administrator')
                    ];
                }, $adminDepartments);

                $adminDepartmentAdministrators = array_column($adminDepartments, null, 'id');
                $adminUsers[] = [
                    "id" => Arr::get($item, 'admin_user.id'),
                    "name" => Arr::get($item, 'admin_user.admin_user.name'),
                    "mobile" => Arr::get($item, 'admin_user.admin_user.mobile'),
                    "sex" => Arr::get($item, 'admin_user.admin_user.sex'),
                    "status" => Arr::get($item, 'admin_user.status'),
                    "statusDesc" => $statusDesc[Arr::get($item, 'admin_user.status')],
                    "adminRoleIds" => $roleId,
                    "adminRoleNames" => implode(",", $roleName),
                    "adminDepartments" => $adminDepartments,
                    "administrator" => Arr::get($adminDepartmentAdministrators, Arr::get($item, 'admin_department_id') . '.administrator'),
                    "created_at" => Arr::get($item, 'admin_user.created_at')
                ];
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
        $data['delete_disabled'] = Arr::get($data, 'admin_users') || Arr::get($data, 'children');
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
                    ->where('customer_id', Access::getCustomerId())
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
        $this->addParam('customer_id', Access::getCustomerId());
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
                    ->where('customer_id', Access::getCustomerId())
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
        $isExist = AdminUserDepartment::query()
            ->whereIn('admin_department_id', is_array($id) ? $id : [$id])
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
        $model = AdminDepartment::query();
        $model = $this->filter($model);
        $adminDepartmentIds = [];
        $data = [];
        $administrator = $this->isAdministrator();
        if (empty($administrator)) {
            $adminDepartmentModel = AdminUserDepartment::query()
                ->where('admin_user_id', Access::getAdminUserId());
            if ($this->getParam('administrator')) {
                //获取用户所有可管理的部门
                $adminDepartmentModel = $adminDepartmentModel->where('administrator', true);
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
        if ($model->isEmpty()) {
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
        $model = AdminDepartment::query();
        $model = $this->filter($model);
        $adminDepartmentIds = [];
        $administrator = $this->isAdministrator();
        $adminDepartmentRoleModel = AdminDepartmentRole::query();
        $adminDepartmentUserModel = AdminUserDepartment::query();
        $adminCategoryDepartmentModel = AdminCategoryDepartment::query();

        if (empty($administrator)) {
            $adminDepartmentIds = AdminUserDepartment::query()
                ->where('admin_user_id', Access::getAdminUserId())
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

        $adminUsers = $adminDepartmentUserModel->get()
            ->groupBy('admin_department_id')
            ->toArray();

        $adminUserIds = $adminDepartmentUserModel
            ->pluck('admin_user_id', 'admin_user_id')
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
            ->whereIn('id', array_values($adminUserIds))
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
            if ($item = $this->allDepartmentItem($value, $adminDepartmentIds, $adminRoles, $adminDepartmentRoles, $adminUsers, $adminUserIds, $adminCategoryDepartments, $adminCategoryDepartmentIds, $existDepartmentIds)) {
                $data[] = $item;
            }
        }
        return $this->response($data);
    }

    protected function allDepartmentItem($value, $adminDepartmentIds, $adminRoles, $adminDepartmentRoles, $adminUsers, $adminUserIds, $adminCategoryDepartments, $adminCategoryDepartmentIds, &$existDepartmentIds)
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
        $adminUser = Arr::get($adminUsers, Arr::get($value, 'id')) ? Arr::pluck(Arr::get($adminUsers, Arr::get($value, 'id')), 'id') : [];

        $data['adminDepartmentRolesCount'] = count($data['admin_role_ids']);
        $data['adminCategoryDepartmentsCount'] = count($data['category_ids']);
        $data['adminUserDepartmentsCount'] = count($adminUser);

        if (Arr::get($value, 'children')) {
            $routes = [];
            $children = Arr::get($value, 'children');
            foreach ($children as $child) {
                if ($item = $this->allDepartmentItem($value, $adminDepartmentIds, $adminRoles, $adminDepartmentRoles, $adminUsers, $adminUserIds, $adminCategoryDepartments, $adminCategoryDepartmentIds, $existDepartmentIds)) {
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
