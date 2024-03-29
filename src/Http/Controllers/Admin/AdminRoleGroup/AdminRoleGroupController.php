<?php

namespace Qz\Admin\Permission\Http\Controllers\Admin\AdminRoleGroup;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Qz\Admin\Permission\Cores\AdminRoleGroup\AdminRoleGroupAdd;
use Qz\Admin\Permission\Cores\AdminRoleGroup\AdminRoleGroupDelete;
use Qz\Admin\Permission\Cores\AdminRoleGroup\AdminRoleGroupUpdate;
use Qz\Admin\Permission\Cores\Common\Filter;
use Qz\Admin\Permission\Http\Controllers\Admin\AdminController;
use Qz\Admin\Permission\Models\AdminRole;
use Qz\Admin\Permission\Models\AdminRoleGroup;

class AdminRoleGroupController extends AdminController
{
    /**
     * @return JsonResponse
     */
    public function get()
    {
        $model = AdminRoleGroup::query();
        $model = $this->filter($model);
        $model = $model
            ->selectRaw('id,name as admin_role_group_name,id admin_role_group_id,created_at')
            ->paginate($this->getPageSize());
        $filter = [];
        if ($this->getParam('filter')) {
            $filter = $this->getChildFilter();
        }
        $model->load([
            'adminRoles' => function (HasMany $hasMany) use ($filter) {
                $hasMany
                    ->selectRaw('name,id,admin_role_group_id,created_at')
                    ->withCount([
                        'adminDepartmentRoles',
                        'adminUserRoles',
                    ]);
                if ($adminRoles = Arr::get($filter, 'adminRoles')) {
                    $hasMany = Filter::init()
                        ->setModel($hasMany)
                        ->setParam($adminRoles)
                        ->run()
                        ->getModel();
                }
            }
        ]);
        foreach ($model->items() as $item) {
            $item->key = Arr::get($item, 'id');
            $item->delete_disabled = false;
            if (Arr::get($item, 'adminRoles') && count(Arr::get($item, 'adminRoles'))) {
                $item->delete_disabled = true;
                foreach (Arr::get($item, 'adminRoles') as $value) {
                    $value->delete_disabled = Arr::get($value, 'department_roles_count') || Arr::get($value, 'admin_user_roles_count');
                    $value->key = Arr::get($item, 'id') . '-' . Arr::get($value, 'id');
                }
            }
        }
        return $this->page($model);
    }

    /**
     * @return JsonResponse
     */
    public function store()
    {
        $validator = Validator::make($this->getParam(), [
            'admin_role_group_name' => [
                'required',
                Rule::unique(AdminRoleGroup::class, 'name')
                    ->where('customer_id', $this->getCustomerId())
                    ->withoutTrashed(),
            ],
        ], [
            'admin_role_group_name.required' => '角色组名称不能为空',
            'admin_role_group_name.unique' => '角色组名称不能重复',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        $this->addParam('customer_id', $this->getCustomerId());
        $this->addParam('name', $this->getParam('admin_role_group_name'));
        $id = AdminRoleGroupAdd::init()
            ->setParam($this->getParam())
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
            'admin_role_group_name' => [
                Rule::unique(AdminRoleGroup::class, 'name')
                    ->where('customer_id', $this->getCustomerId())
                    ->withoutTrashed()
                    ->ignore($this->getParam('id')),
            ],
        ], [
            'admin_role_group_name.unique' => '角色组名称不能重复',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        $this->addParam('name', $this->getParam('admin_role_group_name'));
        $id = AdminRoleGroupUpdate::init()
            ->setId($this->getParam('id'))
            ->setParam($this->getParam())
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
            'id.required' => '请选择要删除的角色组',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        $id = $this->getParam('id');
        $id = is_array($id) ? $id : [$id];
        $isExist = AdminRole::query()
            ->whereIn('admin_role_group_id', $id)
            ->exists();
        if ($isExist) {
            return $this->error("角色组下有角色，不可删除！");
        }
        foreach ($id as $value) {
            AdminRoleGroupDelete::init()
                ->setId($value)
                ->run()
                ->getId();
        }
        return $this->success();
    }

    public function all()
    {
        $param = $this->getParam();
        $select = Arr::get($param, 'select', 'id as value, name as label');
        $model = AdminRoleGroup::query()
            ->selectRaw($select);
        $model = $this->filter($model);
        $model = $model->get();
        return $this->json($model);
    }

    public function allByRole()
    {
        $param = $this->getParam();
        $model = AdminRoleGroup::query();
        $model = $this->filter($model);
        $select = Arr::get($param, 'select', 'id as value, name as label,id');
        $model = $model
            ->selectRaw($select)
            ->get();
        $model->load([
            'adminRoles:id as value,name as label,admin_role_group_id'
        ]);
        return $this->json($model);
    }
}
