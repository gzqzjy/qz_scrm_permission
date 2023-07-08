<?php

namespace Qz\Admin\Permission\Http\Controllers\Admin\AdminRoleGroup;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Qz\Admin\Permission\Cores\AdminRoleGroup\AdminRoleGroupAdd;
use Qz\Admin\Permission\Cores\AdminRoleGroup\AdminRoleGroupDelete;
use Qz\Admin\Permission\Cores\AdminRoleGroup\AdminRoleGroupUpdate;
use Qz\Admin\Permission\Cores\Common\Filter;
use Qz\Admin\Permission\Exceptions\MessageException;
use Qz\Admin\Permission\Facades\Access;
use Qz\Admin\Permission\Http\Controllers\Admin\AdminController;
use Qz\Admin\Permission\Models\AdminRole;
use Qz\Admin\Permission\Models\AdminRoleGroup;

class AdminRoleGroupController extends AdminController
{
    public function get(){
        $model = AdminRoleGroup::query()
            ->where('customer_subsystem_id', Access::getCustomerSubsystemId());

        $model = $this->filter($model);

        $model = $model
            ->selectRaw('id,name as admin_role_group_name,id admin_role_group_id,created_at')
            ->paginate($this->getPageSize());
        $filter = [];
        if ($this->getParam('filter')){
            $filter = $this->getChildFilter();
        }
        $model->load([
            'adminRoles' => function (HasMany $hasMany) use ($filter){
                $hasMany
                    ->selectRaw('name,id,admin_role_group_id,created_at')
                    ->withCount([
                        'departmentRoles',
                        'adminUserCustomerSubsystemRoles'
                    ]);
                if ($adminRoles = Arr::get($filter, 'adminRoles')){
                    $hasMany = Filter::init()
                        ->setModel($hasMany)
                        ->setParam($adminRoles)
                        ->run()
                        ->getModel();
                }
            }
        ]);
        foreach ($model->items() as $item){
            $item->key = Arr::get($item, 'id');
            $item->deleted_isabled = false;
            if (Arr::get($item, 'adminRoles') && count(Arr::get($item, 'adminRoles'))){
                $item->deleted_isabled = true;
                foreach (Arr::get($item, 'adminRoles') as $value){
                    $value->deleted_isabled = Arr::get($value, 'department_roles_count') || Arr::get($value, 'admin_user_customer_subsystem_roles_count');
                    $value->key = Arr::get($item, 'id') . '-' . Arr::get($value, 'id');
                }
            }
        }


        return $this->page($model);
    }

    /**
     * @return JsonResponse
     * @throws MessageException
     */
    public function store()
    {
        $validator = Validator::make($this->getParam(), [
            'admin_role_group_name' => [
                'required',
                Rule::unique(AdminRoleGroup::class, 'name')
                    ->where('customer_subsystem_id', Access::getCustomerSubsystemId())
                    ->withoutTrashed(),
            ],
        ], [
            'admin_role_group_name.required' => '角色组名称不能为空',
            'admin_role_group_name.unique' => '角色组名称不能重复',
        ]);
        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }
        $this->addParam('customer_subsystem_id', Access::getCustomerSubsystemId());
        $this->addParam('name', $this->getParam('admin_role_group_name'));
        $id = AdminRoleGroupAdd::init()
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
            'admin_role_group_name' => [
                Rule::unique(AdminRoleGroup::class, 'name')
                    ->withoutTrashed()
                    ->ignore($this->getParam('id'))
                    ->where('customer_subsystem_id', Access::getCustomerSubsystemId())
            ],
        ], [
            'admin_role_group_name.unique' => '角色组名称不能重复',
        ]);
        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }
        $this->addParam('name', $this->getParam('admin_role_group_name'));
        $id = AdminRoleGroupUpdate::init()
            ->setId($this->getParam('id'))
            ->setParam($this->getParam())
            ->run()
            ->getId();
        return $this->success(compact('id'));
    }

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
            throw new MessageException($validator->errors()->first());
        }
        $id = $this->getParam('id');
        $id = is_array($id) ? $id : [$id];
        $isExist = AdminRole::query()
            ->whereIn('admin_role_group_id', $id)
            ->exists();
        if ($isExist){
            throw new MessageException("角色组下有角色，不可删除！");
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
            ->where('customer_subsystem_id', Access::getCustomerSubsystemId())
            ->selectRaw($select);
        $model = $this->filter($model);
        $model = $model->get();
        return $this->response($model);
    }

    public function allByRole()
    {
        $param = $this->getParam();
        $model = AdminRoleGroup::query()
            ->where('customer_subsystem_id', Access::getCustomerSubsystemId());
        $model = $this->filter($model);
        $select = Arr::get($param, 'select', 'id as value, name as label,id');
        $model = $model
            //->select(['id', 'name'])
            ->selectRaw($select)
            ->get();
        $model->load([
            'adminRoles:id as value,name as label,admin_role_group_id'
        ]);

        return $this->response($model);
    }
}
