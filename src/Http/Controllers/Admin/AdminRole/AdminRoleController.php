<?php

namespace Qz\Admin\Permission\Http\Controllers\Admin\AdminRole;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\AddShortUrlResponseBody\data;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Qz\Admin\Permission\Cores\AdminRole\AdminRoleAdd;
use Qz\Admin\Permission\Cores\AdminRole\AdminRoleDelete;
use Qz\Admin\Permission\Cores\AdminRole\AdminRoleUpdate;
use Qz\Admin\Permission\Cores\AdminRole\GetMenuByAdminRole;
use Qz\Admin\Permission\Exceptions\MessageException;
use Qz\Admin\Permission\Facades\Access;
use Qz\Admin\Permission\Http\Controllers\Admin\AdminController;
use Qz\Admin\Permission\Models\AdminDepartmentRole;
use Qz\Admin\Permission\Models\AdminRole;
use Qz\Admin\Permission\Models\AdminRoleGroup;
use Qz\Admin\Permission\Models\AdminRoleRequest;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystemRole;

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
        if ($permissions = $this->getParam('permission')){
            $adminMenuIds = $adminPageColumnIds = $adminPageOptionIds = [];
            foreach ($permissions as $permission){
                list($itemAdminMenuIds, $itemAdminPageColumnIds, $itemAdminPageOptionIds) = $this->getPermission($permission);
                $adminMenuIds = array_merge($itemAdminMenuIds, $adminMenuIds);
                $adminPageColumnIds = array_merge($itemAdminPageColumnIds, $adminPageColumnIds);
                $adminPageOptionIds = array_merge($itemAdminPageOptionIds, $adminPageOptionIds);
            }
            $this->addParam('admin_role_menu', array_unique($adminMenuIds));
            $this->addParam('admin_role_page_column', array_unique($adminPageColumnIds));
            $this->addParam('admin_role_page_option', array_unique($adminPageOptionIds));
        }
        if ($dataPermissions = $this->getParam('data_permission')){
            $adminRoleRequests = [];
            $character = AdminRoleRequest::CHARACTER;
            foreach ($dataPermissions as $dataPermission){
                $adminRoleRequests[] = [
                    'admin_request_id' => Arr::get($dataPermission, 'admin_request_id'),
                    'type' => implode($character, Arr::get($dataPermission, 'actions'))
                ];
            }
            if ($adminRoleRequests){
                $this->addParam('admin_role_request', $adminRoleRequests);
            }
        }

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
        if ($permissions = $this->getParam('permission')){
            $adminMenuIds = $adminPageColumnIds = $adminPageOptionIds = [];
            foreach ($permissions as $permission){
                list($itemAdminMenuIds, $itemAdminPageColumnIds, $itemAdminPageOptionIds) = $this->getPermission($permission);
                $adminMenuIds = array_merge($itemAdminMenuIds, $adminMenuIds);
                $adminPageColumnIds = array_merge($itemAdminPageColumnIds, $adminPageColumnIds);
                $adminPageOptionIds = array_merge($itemAdminPageOptionIds, $adminPageOptionIds);
            }
            $this->addParam('admin_role_menu', array_unique($adminMenuIds));
            $this->addParam('admin_role_page_column', array_unique($adminPageColumnIds));
            $this->addParam('admin_role_page_option', array_unique($adminPageOptionIds));
        }
        if ($dataPermissions = $this->getParam('data_permission')){
            $adminRoleRequests = [];
            $character = AdminRoleRequest::CHARACTER;
            foreach ($dataPermissions as $dataPermission){
                $adminRoleRequests[] = [
                    'admin_request_id' => Arr::get($dataPermission, 'admin_request_id'),
                    'type' => implode($character, Arr::get($dataPermission, 'actions'))
                ];
            }
            if ($adminRoleRequests){
                $this->addParam('admin_role_request', $adminRoleRequests);
            }
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
        $isExist = AdminUserCustomerSubsystemRole::query()
            ->whereIn('admin_role_id', $id)
            ->exists();
        if ($isExist){
            throw new MessageException("角色下有员工，不可删除！");
        }
        $isExist = AdminDepartmentRole::query()
            ->whereIn('admin_role_id', $id)
            ->exists();
        if ($isExist){
            throw new MessageException("角色下有部门，不可删除！");
        }

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
        $groupModel = AdminRoleGroup::query()
            ->where('customer_subsystem_id', Access::getCustomerSubsystemId())
            ->selectRaw($select)
            ->get();
        if (empty($groupModel)){
            return $this->response([]);
        }
        $model = AdminRole::query()
            ->where('customer_subsystem_id', Access::getCustomerSubsystemId())
            ->selectRaw($select . ',admin_role_group_id');
        $model = $this->filter($model);
        $adminDepartmentIds = [];
        if ($this->getParam('admin_departments')){
            $adminDepartmentIds = Arr::pluck($this->getParam('admin_departments'), 'id');
            if (empty($adminDepartmentIds)){
                return $this->response([]);
            }
            $model->whereHas('departmentRoles', function (Builder $builder) use ($adminDepartmentIds){
                $builder->whereIn('admin_department_id', $adminDepartmentIds);
            });
        }
        if ($this->getParam('admin_department_id')){
            $adminDepartmentId = $this->getParam('admin_department_id');
            $model->whereHas('departmentRoles', function (Builder $builder) use ($adminDepartmentId){
                $builder->where('admin_department_id', $adminDepartmentId);
            });
        }
        $model = $model->get();
        $model = $model
            ->groupBy('admin_role_group_id')
            ->toArray();
        foreach ($groupModel as &$item){
            $pid = Arr::get($item, 'value');
            if ($children = Arr::get($model, $pid)){
                Arr::set($item, 'children', array_values($children));
            }
            Arr::set($item, 'value', 'admin_role_group_'.$pid);
        }
        return $this->response($groupModel);
    }

    protected function permission()
    {
        if ($this->getParam('type') == 'data'){
            //数据权限
            if (empty($this->getParam('id'))){
                return $this->success([
                    [
                        "adminRequestId" => 0,
                        "actions" => []
                    ]
                ]);
            }
            $adminRoleRequests = AdminRoleRequest::query()
                ->where('admin_role_id', $this->getParam('id'))
                ->get();
            $data = [];
            foreach ($adminRoleRequests as $adminRoleRequest){
                $data[] = [
                    "adminRequestId" => Arr::get($adminRoleRequest, 'admin_request_id'),
                    "actions" => Arr::get($adminRoleRequest, 'type') ? explode(AdminRoleRequest::CHARACTER, Arr::get($adminRoleRequest, 'type')) :[],
                ];
            }
            if (empty($data)){
                return $this->success([
                    [
                        "adminRequestId" => 0,
                        "actions" => []
                    ]
                ]);
            }
            return $this->success($data);
        }else{
            //功能权限
            $permission = GetMenuByAdminRole::init();
            if ($this->getParam('id')){
                $permission->setAdminRoleIds([$this->getParam('id')]);
            }
            $menus = $permission
                ->setSubsystemId(Access::getSubsystemId())
                ->run()
                ->getMenus();

            return $this->success($menus);
        }
    }

    protected function permissionItem($value, $menuIds, $pageColumnIds, $pageOptionIds)
    {
        $data = [];
        Arr::set($data, 'label', Arr::get($value, 'name'));
        Arr::set($data, 'value', Arr::get($value, 'id'));
        if (in_array(Arr::get($value, 'id'), $menuIds)){
            Arr::set($data, 'check', true);
        }else{
            Arr::set($data, 'check', false);
        }
        if (Arr::get($value, 'admin_page_id')) {
            Arr::set($data, 'admin_page_id', Arr::get($value, 'admin_page_id'));
        }
        $adminPageOptions = Arr::get($value, 'admin_page.admin_page_options');
        $allCheckedOption = $allCheckedColumn = "null";
        if (!empty($adminPageOptions)) {
            Arr::set($data, 'options', array_map(function ($option) use ($pageOptionIds){
                return [
                    'label' => Arr::get($option, 'name'),
                    'value' => Arr::get($option, 'id'),
                    'check' => in_array(Arr::get($option, 'id'), $pageOptionIds) ? true : false
                ];
            }, $adminPageOptions));
            $check = array_unique(Arr::pluck(Arr::get($data, 'options'), 'check'));
            if (count($check) > 1){
                $allCheckedOption = "some";
            }else{
                $allCheckedOption = Arr::get($check, '0') ? "all" : "null";
            }
            Arr::set($data, 'options.allCheck', $allCheckedOption);
        }
        $adminPageColumns = Arr::get($value, 'admin_page.admin_page_columns');
        if (!empty($adminPageColumns)) {
            Arr::set($data, 'columns', array_map(function ($column) use ($pageColumnIds) {
                return [
                    'label' => Arr::get($column, 'name'),
                    'value' => Arr::get($column, 'id'),
                    'check' => in_array(Arr::get($column, 'id'), $pageColumnIds) ? true : false
                ];
            }, $adminPageColumns));
            $check = array_unique(Arr::pluck(Arr::get($data, 'columns'), 'check'));
            if (count($check) > 1){
                $allCheckedColumn = "some";
            }else{
                $allCheckedColumn = Arr::get($check, '0') ? "all" : "null";
            }
            Arr::set($data, 'columns.allCheck', $allCheckedColumn);
        }
        if ($allCheckedOption == "all" && $allCheckedColumn == "all"){
            Arr::set($data, 'allCheck', "all");
        }elseif($allCheckedOption == "some" || $allCheckedColumn == "some"){
            Arr::set($data, 'allCheck', "some");
        }elseif (!empty($adminPageColumns) || !empty($adminPageOptions)){
            Arr::set($data, 'allCheck', "null");
        }else{
            Arr::set($data, 'allCheck', Arr::get($data, 'check') ? "all" : "null");
        }

        if (Arr::get($value, 'children')) {
            $routes = [];
            $children = Arr::get($value, 'children');
            foreach ($children as $child) {
                $routes[] = $this->permissionItem($child, $menuIds, $pageColumnIds, $pageOptionIds);
            }
            if (!empty($routes)) {
                Arr::set($data, 'children', $routes);
                $check = array_unique(Arr::pluck($routes, 'allCheck'));
                if (count($check) > 1){
                    Arr::set($data, 'allCheck', "some");
                }else{
                    Arr::set($data, 'allCheck', Arr::get($check, '0'));
                }
            }
        }
        return $data;
    }

    protected function getPermission($permission)
    {
        $adminMenuIds = $adminPageColumnIds = $adminPageOptionIds = [];
        if (Arr::get($permission, 'check')){
            $adminMenuIds[] = Arr::get($permission, 'value');
        }
        if ($columns = Arr::get($permission, 'columns')){
            $adminPageColumnIds = array_map(function ($column){
                return Str::replace('column_', '', Arr::get($column, 'value'));
            }, array_filter($columns, function ($column){
                if (Arr::get($column, 'check')){
                    return Arr::get($column, 'value');
                }
            }));
        }
        if ($options = Arr::get($permission, 'options')){
            $adminPageOptionIds = array_map(function ($option){
                return Str::replace('option_', '', Arr::get($option, 'value'));
            }, array_filter($options, function ($option){
                if (Arr::get($option, 'check')){
                    return Arr::get($option, 'value');
                }
            }));
        }
        if ($children = Arr::get($permission, 'children')){
            foreach ($children as $child) {
                list($adminItemMenuIds, $adminItemPageColumnIds, $adminItemPageOptionIds) = $this->getPermission($child);
                $adminMenuIds = array_merge($adminItemMenuIds, $adminMenuIds);
                $adminPageColumnIds = array_merge($adminItemPageColumnIds, $adminPageColumnIds);
                $adminPageOptionIds = array_merge($adminItemPageOptionIds, $adminPageOptionIds);
            }
        }
        return [$adminMenuIds,$adminPageColumnIds,$adminPageOptionIds];
    }
}
