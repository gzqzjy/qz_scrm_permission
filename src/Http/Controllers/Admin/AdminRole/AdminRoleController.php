<?php

namespace Qz\Admin\Permission\Http\Controllers\Admin\AdminRole;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Qz\Admin\Permission\Cores\AdminRole\AdminRoleAdd;
use Qz\Admin\Permission\Cores\AdminRole\AdminRoleDelete;
use Qz\Admin\Permission\Cores\AdminRole\AdminRoleUpdate;
use Qz\Admin\Permission\Exceptions\MessageException;
use Qz\Admin\Permission\Facades\Access;
use Qz\Admin\Permission\Http\Controllers\Admin\AdminController;
use Qz\Admin\Permission\Models\AdminRole;

class AdminRoleController extends AdminController
{
    public function get()
    {
        $model = AdminRole::query()
            ->where('customer_subsystem_id', Access::getCustomerSubsystemId());

        $model = $this->filter($model);
        $model = $model
            ->get();
        $model->loadCount([
            'departmentRoles',
            'adminUserCustomerSubsystemRoles'
        ]);
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
                Rule::unique(AdminRole::class)
                    ->where('customer_subsystem_id', Access::getCustomerSubsystemId())
                    ->withoutTrashed(),
            ],
            'admin_role_group_id' => [
                'required',
                Rule::exists('admin_role_groups', 'id')
                    ->withoutTrashed(),
            ]
        ], [
            'name.required' => '角色名称不能为空',
            'name.unique' => '角色名称不能重复',
            'admin_role_group_id.required' => '角色组不能为空',
            'admin_role_group_id.exists' => '角色组不存在',
        ]);
        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }
        $this->addParam('customer_subsystem_id', Access::getCustomerSubsystemId());
        $id = AdminRoleAdd::init()
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
                Rule::exists('admin_roles', 'id')
                    ->withoutTrashed(),
            ],
            'name' => [
                'required',
                Rule::unique(AdminRole::class)
                    ->withoutTrashed()
                    ->ignore($this->getParam('id'))
                    ->where('customer_subsystem_id', Access::getCustomerSubsystemId())
            ],
            'admin_role_group_id' => [
                'required',
                Rule::exists('admin_role_groups', 'id')
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
            throw new MessageException($validator->errors()->first());
        }
        $id = AdminRoleUpdate::init()
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
                AdminRoleDelete::init()
                    ->setId($value)
                    ->run()
                    ->getId();
            }
            return $this->success();
        }
        AdminRoleDelete::init()
            ->setId($id)
            ->run()
            ->getId();
        return $this->success();
    }

    public function all()
    {
        $param = $this->getParam();
        $select = Arr::get($param, 'select', 'id as value, name as label');
        $model = AdminRole::query()
            ->where('customer_subsystem_id', Access::getCustomerSubsystemId())
            ->selectRaw($select);
        $model = $this->filter($model);
        if ($this->getParam('admin_departments')){
            $adminDepartmentIds = Arr::pluck($this->getParam('admin_departments'), 'id');
            if (empty($adminDepartmentIds)){
                return $this->response([]);
            }
            $model->whereHas('departmentRoles', function (Builder $builder) use ($adminDepartmentIds){
                $builder->whereIn('admin_department_id', $adminDepartmentIds);
            });
        }
        $model = $model->get();
        return $this->response($model);
    }
}
