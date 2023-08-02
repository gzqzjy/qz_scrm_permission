<?php

namespace Qz\Admin\Permission\Http\Controllers\Admin\AdminRole;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Qz\Admin\Permission\Cores\AdminRole\AdminRoleAdd;
use Qz\Admin\Permission\Cores\AdminRole\AdminRoleDelete;
use Qz\Admin\Permission\Cores\AdminRole\AdminRoleUpdate;
use Qz\Admin\Permission\Cores\Common\Filter;
use Qz\Admin\Permission\Http\Controllers\Admin\AdminController;
use Qz\Admin\Permission\Models\AdminDepartmentRole;
use Qz\Admin\Permission\Models\AdminRole;
use Qz\Admin\Permission\Models\AdminRoleGroup;
use Qz\Admin\Permission\Models\AdminRoleMenu;
use Qz\Admin\Permission\Models\AdminRolePageColumn;
use Qz\Admin\Permission\Models\AdminRolePageOption;
use Qz\Admin\Permission\Models\AdminRoleRequest;
use Qz\Admin\Permission\Models\AdminUserRole;

class AdminRoleController extends AdminController
{
    public function get()
    {
        $model = AdminRole::query();
        $model = $this->filter($model);
        $model = $model
            ->get();
        $model->loadCount([
            'adminDepartmentRoles',
            'adminUserRoles'
        ]);
        foreach ($model as $value) {
            $value->deleteDisabled = false;
            if (Arr::get($value, 'admin_department_roles_count')) {
                $value->deleteDisabled = true;
            } elseif (Arr::get($value, 'admin_user_roles_count')) {
                $value->deleteDisabled = true;
            }
        }
        return $this->success($model->toArray());
    }

    /**
     * @return JsonResponse
     */
    public function store()
    {
        $validator = Validator::make($this->getParam(), [
            'name' => [
                'required',
                Rule::unique(AdminRole::class)
                    ->where('customer_id', $this->getCustomerId())
                    ->withoutTrashed(),
            ],
            'admin_role_group_id' => [
                'required',
                Rule::exists(AdminRoleGroup::class, 'id')
                    ->where('customer_id', $this->getCustomerId())
                    ->withoutTrashed(),
            ]
        ], [
            'name.required' => '角色名称不能为空',
            'name.unique' => '角色名称不能重复',
            'admin_role_group_id.required' => '角色组不能为空',
            'admin_role_group_id.exists' => '角色组不存在',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        $id = AdminRoleAdd::init()
            ->setCustomerId($this->getCustomerId())
            ->setParam($this->getParam())
            ->setAdminMenuIds($this->getParam('permission.admin_menu_ids'))
            ->setAdminPageOptionIds($this->getParam('permission.admin_page_option_ids'))
            ->setAdminPageColumnIds($this->getParam('permission.admin_page_column_ids'))
            ->setAdminRequests($this->getParam('permission.admin_requests'))
            ->run()
            ->getId();
        return $this->success(compact('id'));
    }

    /**
     * @return JsonResponse
     */
    public function update()
    {
        $validator = Validator::make($this->getParam(), [
            'id' => [
                'required',
                Rule::exists(AdminRole::class, 'id')
                    ->where('customer_id', $this->getCustomerId())
                    ->withoutTrashed(),
            ],
            'name' => [
                'required',
                Rule::unique(AdminRole::class)
                    ->where('customer_id', $this->getCustomerId())
                    ->withoutTrashed()
                    ->ignore($this->getParam('id')),
            ],
            'admin_role_group_id' => [
                'required',
                Rule::exists(AdminRoleGroup::class, 'id')
                    ->where('customer_id', $this->getCustomerId())
                    ->withoutTrashed(),
            ]
        ], [
            'id.required' => '角色id不能为空',
            'id.exists' => '角色不存在',
            'name.required' => '角色名称不能为空',
            'name.unique' => '角色名称不能重复',
            'admin_role_group_id.required' => '角色组不能为空',
            'admin_role_group_id.exists' => '角色组不存在',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        $id = AdminRoleUpdate::init()
            ->setId($this->getParam('id'))
            ->setParam($this->getParam())
            ->setAdminMenuIds($this->getParam('permission.admin_menu_ids'))
            ->setAdminPageOptionIds($this->getParam('permission.admin_page_option_ids'))
            ->setAdminPageColumnIds($this->getParam('permission.admin_page_column_ids'))
            ->setAdminRequests($this->getParam('permission.admin_requests'))
            ->run()
            ->getId();
        return $this->success(compact('id'));
    }

    /**
     * @return JsonResponse
     */
    public function destroy()
    {
        $validator = Validator::make($this->getParam(), [
            'id' => [
                'required',
            ],
        ], [
            'id.required' => '请选择要删除的角色',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        $id = $this->getParam('id');
        $id = is_array($id) ? $id : [$id];
        $isExist = AdminUserRole::query()
            ->whereIn('admin_role_id', $id)
            ->exists();
        if ($isExist) {
            return $this->error("角色下有员工，不可删除！");
        }
        $isExist = AdminDepartmentRole::query()
            ->whereIn('admin_role_id', $id)
            ->exists();
        if ($isExist) {
            return $this->error("角色下有部门，不可删除！");
        }
        foreach ($id as $value) {
            AdminRoleDelete::init()
                ->setId($value)
                ->run()
                ->getId();
        }
        return $this->success();
    }

    public function all()
    {
        $param = $this->getParam();
        $model = AdminRoleGroup::query();
        $model = $this->filter($model);
        $model = $model->selectRaw('id,name as label')
            ->whereHas('adminRoles')
            ->get();
        $model->load([
            'adminRoles' => function (HasMany $hasMany) use ($param) {
                $select = Arr::get($param, 'select', 'id as value, name as label');
                $hasMany->selectRaw($select . ',admin_role_group_id');
                return Filter::init()
                    ->setModel($hasMany)
                    ->setParam(Arr::get($param, 'filter'))
                    ->run()
                    ->getModel();
            }
        ]);
        foreach ($model as $value) {
            $value->options = $value->adminRoles;
        }
        return $this->json($model->toArray());
    }

    public function pagePermission()
    {
        $param = $this->getParam();
        $id = Arr::get($param, 'admin_role_id');
        $adminMenuIds = AdminRoleMenu::query()
            ->where('admin_role_id', $id)
            ->where('admin_role_id', '>', 0)
            ->pluck('admin_menu_id')
            ->toArray();
        $adminPageColumnIds = AdminRolePageColumn::query()
            ->where('admin_role_id', $id)
            ->where('admin_role_id', '>', 0)
            ->pluck('admin_page_column_id')
            ->toArray();
        $adminPageOptionIds = AdminRolePageOption::query()
            ->where('admin_role_id', $id)
            ->where('admin_role_id', '>', 0)
            ->pluck('admin_page_option_id')
            ->toArray();
        return $this->success(compact('adminMenuIds', 'adminPageOptionIds', 'adminPageColumnIds'));
    }

    public function requestPermission()
    {
        $param = $this->getParam();
        $id = Arr::get($param, 'admin_role_id');
        $adminRequests = AdminRoleRequest::query()
            ->select(['admin_request_id', 'type'])
            ->where('admin_role_id', $id)
            ->orderBy('admin_request_id')
            ->get()
            ->toArray();
        if (empty($adminRequests)) {
            $adminRequests = AdminRoleRequest::query()
                ->select(['admin_request_id', 'type'])
                ->where('admin_role_id', 0)
                ->orderBy('admin_request_id')
                ->get()
                ->toArray();
        }
        return $this->success(compact('adminRequests'));
    }
}
