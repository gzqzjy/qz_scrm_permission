<?php

namespace Qz\Admin\Permission\Http\Controllers\Admin\AdminDepartment;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Qz\Admin\Permission\Cores\AdminDepartment\AdminDepartmentAdd;
use Qz\Admin\Permission\Cores\AdminDepartment\AdminDepartmentDelete;
use Qz\Admin\Permission\Cores\AdminDepartment\AdminDepartmentUpdate;
use Qz\Admin\Permission\Cores\AdminDepartment\GetSubAdminDepartmentIdsByAdminDepartmentIds;
use Qz\Admin\Permission\Cores\AdminDepartment\GetTreeDepartmentList;
use Qz\Admin\Permission\Exceptions\MessageException;
use Qz\Admin\Permission\Facades\Access;
use Qz\Admin\Permission\Http\Controllers\Admin\AdminController;
use Qz\Admin\Permission\Models\AdminDepartment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Qz\Admin\Permission\Models\AdminUser;
use Qz\Admin\Permission\Models\AdminUserDepartment;

class AdminDepartmentController extends AdminController
{
    public function get()
    {
        $model = AdminDepartment::query();
        if (!$this->isAdministrator()) {
            $model->whereHas('adminUserDepartments', function (Builder $builder) {
                $builder->where('admin_user_id', $this->getLoginAdminUserId())
                    ->where('administrator', true);
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
                    $builder->whereHas('adminUserDepartments', function (Builder $builder) {
                        $builder->where('admin_user_id', $this->getLoginAdminUserId())
                            ->where('administrator', true);
                    });
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

    protected function format(Collection $model)
    {
        foreach ($model as $value) {
            if (!$value->adminUserDepartments->isEmpty() || !$value->children->isEmpty()) {
                $value->deleteDisabled = true;
            }
            if (!$value->adminUserDepartments->isEmpty()) {
                $adminUsers = [];
                foreach ($value->adminUserDepartments as $adminUserDepartment) {
                    if (!$adminUserDepartment->adminUser) {
                        continue;
                    }
                    $adminUserDepartment->adminUser->adminRoleIds = $adminUserDepartment->adminUser->adminUserRoles->pluck('admin_role_id');
                    if ($adminUserDepartment->adminUser->status != AdminUser::STATUS_LEAVED) {
                        $adminUserDepartment->adminUser->delete_disabled = true;
                    }
                    $adminUsers[] = $adminUserDepartment->adminUser;
                }
                $value->adminUsers = $adminUsers;
            }
            $value->adminRoleIds = $value->adminDepartmentRoles->pluck('admin_role_id');
            $value->categoryIds = $value->adminCategoryDepartments->pluck('category_id');
            if (!$value->children->isEmpty()) {
                $value->children = $this->format($value->children);
            }
            $value->adminUserDepartmentsCount = $this->totalAdminUserDepartments($value);
        }
        return $model;
    }

    protected function totalAdminUserDepartments($adminDepartment)
    {
        $adminUserDepartments = (int) $adminDepartment->admin_user_departments_count;
        if ($adminDepartment->children && !$adminDepartment->children->isEmpty()) {
            foreach ($adminDepartment->children as $child) {
                $adminUserDepartments += $this->totalAdminUserDepartments($child);
            }
        }
        return $adminUserDepartments;
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
                    ->where('customer_id', $this->getCustomerId())
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
        $this->addParam('customer_id', $this->getCustomerId());
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
                    ->where('customer_id', $this->getCustomerId())
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

    /**
     * @return JsonResponse
     * @throws MessageException
     */
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
        $model = AdminDepartment::query();
        $model = $this->filter($model);
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
                return $this->json($data);
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
            return $this->json($data);
        }
        $model = $model->toArray();

        $data = GetTreeDepartmentList::init()
            ->setAdminDepartments($model)
            ->run()
            ->getTreeAdminDepartments();
        return $this->json($data);
    }
}
