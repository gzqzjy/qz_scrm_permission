<?php

namespace Qz\Admin\Permission\Http\Controllers\Admin\AdminDepartment;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Qz\Admin\Permission\Cores\AdminDepartment\AdminDepartmentAdd;
use Qz\Admin\Permission\Cores\AdminDepartment\AdminDepartmentDelete;
use Qz\Admin\Permission\Cores\AdminDepartment\AdminDepartmentUpdate;
use Qz\Admin\Permission\Cores\AdminMenu\AdminMenuAdd;
use Qz\Admin\Permission\Cores\AdminMenu\AdminMenuDelete;
use Qz\Admin\Permission\Cores\AdminMenu\AdminMenuUpdate;
use Qz\Admin\Permission\Exceptions\MessageException;
use Qz\Admin\Permission\Facades\Access;
use Qz\Admin\Permission\Http\Controllers\Admin\AdminController;
use Qz\Admin\Permission\Models\AdminDepartment;
use Qz\Admin\Permission\Models\AdminMenu;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Qz\Admin\Permission\Models\AdminRole;

class AdminDepartmentController extends AdminController
{
    public function get()
    {
        $model = AdminDepartment::query()
            ->where('customer_subsystem_id', Access::getCustomerSubsystemId());
        $model = $this->filter($model);
        $model = $model->paginate($this->getPageSize());
        call_user_func([$model, 'load'], [
            'adminCategoryDepartments',
            'adminDepartmentRoles',
        ]);
        $model->loadCount([
            'adminDepartmentRoles',
            'adminCategoryDepartments',
            'adminUserCustomerSubsystemDepartments'
        ]);
        foreach ($model->items() as &$item){
            $item->category_ids = Arr::pluck(Arr::get($item, 'adminCategoryDepartments'), 'category_id');
            $item->admin_role_ids = Arr::pluck(Arr::get($item, 'adminDepartmentRoles'), 'admin_role_id');
        }
//        call_user_func_array([$model, 'load'], [
//            'parent'
//        ]);
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
        if ($this->getParam('pid')){
            $adminDepartment = AdminDepartment::query()
                ->find($this->getParam('pid'));
            $this->addParam('level', Arr::get($adminDepartment, 'level') + 1);
        }else{
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
        if ($this->getParam('pid')){
            $adminDepartment = AdminDepartment::query()
                ->find($this->getParam('pid'));
            $this->addParam('level', Arr::get($adminDepartment, 'level') + 1);
        }else{
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
            ->where('pid', 0)
            ->where('customer_subsystem_id', Access::getCustomerSubsystemId());
        $model = $this->filter($model);
        $model = $model
            ->get();
        $model->load([
            'children'
        ]);
        $data = [];
        foreach ($model as $value) {
            $data[] = $this->departmentItem($value);
        }
        return $this->response($data);
    }



    protected function departmentItem($value)
    {
        $data = [
            'title' => Arr::get($value, 'name'),
            'value' => Arr::get($value, 'id'),
        ];
        if (Arr::get($value, 'children')) {
            $routes = [];
            $children = Arr::get($value, 'children');
            foreach ($children as $child) {
                $routes[] = $this->departmentItem($child);
            }
            if (!empty($routes)) {
                Arr::set($data, 'children', $routes);
            }
        }
        return $data;
    }

    public function departmentList()
    {
        $model = AdminDepartment::query()
            ->where('pid', 0)
            ->where('customer_subsystem_id', Access::getCustomerSubsystemId());
        $model = $this->filter($model);
        $model = $model
            ->get();
        $model->load([
            'childrenDepartmentAndAdminUsers',
            'adminUserCustomerSubsystemDepartments.adminUserCustomerSubsystem.adminUser',
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
        foreach ($model as $value) {
            $data[] = $this->item($value);
        }
        return $this->success($data);
    }

    protected function item($value)
    {
        $data = Arr::except($value, ['children_department_and_admin_users','admin_user_customer_subsystem_departments','admin_category_departments','admin_department_roles']);
//        $data = Arr::only($value, ['id','name','pid','level','customer_subsystem_id','created_at','updated_at','admin_department_roles_count','admin_category_departments_count','admin_user_customer_subsystem_departments_count']);
        $data['category_ids'] = Arr::pluck(Arr::get($value, 'admin_category_departments'), 'category_id');
        $data['admin_role_ids'] = Arr::pluck(Arr::get($value, 'admin_department_roles'), 'admin_role_id');
        if (Arr::get($value, 'children_department_and_admin_users')) {
            $routes = [];
            $children = Arr::get($value, 'children_department_and_admin_users');
            foreach ($children as $child) {
                $routes[] = $this->item($child);
            }
            if (!empty($routes)) {
                Arr::set($data, 'children', $routes);
            }
        }
        if (Arr::get($value, 'admin_user_customer_subsystem_departments')){
            $adminUsers = [];
            foreach (Arr::get($value, 'admin_user_customer_subsystem_departments') as $item){
                $adminUsers[] = Arr::get($item, 'admin_user_customer_subsystem.admin_user');
            }
            Arr::set($data, 'admin_users', $adminUsers);
        }
        Log::info('返回data', [$data]);
        return $data;
    }
}
