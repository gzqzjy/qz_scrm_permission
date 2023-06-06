<?php

namespace Qz\Admin\Permission\Http\Controllers\Admin\AdminRoleGroup;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Qz\Admin\Permission\Cores\AdminRoleGroup\AdminRoleGroupAdd;
use Qz\Admin\Permission\Cores\AdminRoleGroup\AdminRoleGroupDelete;
use Qz\Admin\Permission\Cores\AdminRoleGroup\AdminRoleGroupUpdate;
use Qz\Admin\Permission\Cores\AdminUser\AdminUserAdd;
use Qz\Admin\Permission\Cores\AdminUser\AdminUserDelete;
use Qz\Admin\Permission\Cores\AdminUser\AdminUserUpdate;
use Qz\Admin\Permission\Exceptions\MessageException;
use Qz\Admin\Permission\Facades\Access;
use Qz\Admin\Permission\Http\Controllers\Admin\AdminController;
use Qz\Admin\Permission\Models\AdminRoleGroup;
use Qz\Admin\Permission\Models\AdminUser;

class AdminRoleGroupController extends AdminController
{
    public function get()
    {
        $model = AdminRoleGroup::query()
            ->where('customer_subsystem_id', Access::getCustomerSubsystemId());

        $model = $this->filter($model);
        $model = $model->paginate($this->getPageSize());
        return $this->page($model);
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
                Rule::unique(AdminRoleGroup::class)
                    ->where('customer_subsystem_id', Access::getCustomerSubsystemId())
                    ->withoutTrashed(),
            ],
        ], [
            'name.required' => '角色组名称不能为空',
            'name.unique' => '角色组名称不能重复',
        ]);
        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }
        $this->addParam('customer_subsystem_id', Access::getCustomerSubsystemId());
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
            'name' => [
                Rule::unique(AdminRoleGroup::class)
                    ->withoutTrashed()
                    ->ignore($this->getParam('id'))
                    ->where('customer_subsystem_id', Access::getCustomerSubsystemId())
            ],
        ], [
            'name.unique' => '角色组名称不能重复',
        ]);
        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }
        $id = AdminRoleGroupUpdate::init()
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
                AdminRoleGroupDelete::init()
                    ->setId($value)
                    ->run()
                    ->getId();
            }
            return $this->success();
        }
        AdminRoleGroupDelete::init()
            ->setId($id)
            ->run()
            ->getId();
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
