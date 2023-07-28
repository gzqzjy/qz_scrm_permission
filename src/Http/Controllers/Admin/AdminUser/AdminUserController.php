<?php

namespace Qz\Admin\Permission\Http\Controllers\Admin\AdminUser;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Qz\Admin\Permission\Cores\AdminUser\AdminMenuIdsByAdminUserIdGet;
use Qz\Admin\Permission\Cores\AdminUser\AdminPageColumnIdsByAdminUserIdGet;
use Qz\Admin\Permission\Cores\AdminUser\AdminPageOptionIdsByAdminUserIdGet;
use Qz\Admin\Permission\Cores\AdminUser\AdminRequestsByAdminUserIdGet;
use Qz\Admin\Permission\Cores\AdminUser\AdminUserAdd;
use Qz\Admin\Permission\Cores\AdminUser\AdminUserDelete;
use Qz\Admin\Permission\Cores\AdminUser\AdminUserUpdate;
use Qz\Admin\Permission\Exceptions\MessageException;
use Qz\Admin\Permission\Facades\Access;
use Qz\Admin\Permission\Http\Controllers\Admin\AdminController;
use Qz\Admin\Permission\Models\AdminUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminUserController extends AdminController
{
    public function get()
    {
        $model = AdminUser::query();
        $model = $this->filter($model);
        if ($this->getParam('admin_department_id')) {
            $model = $model->whereHas('adminUsers', function (Builder $builder) {
                $builder->whereHas('adminUserDepartments', function (Builder $builder) {
                    $builder->where('admin_department_id', $this->getParam('admin_department_id'));
                });
            });
        }
        $model = $model->get();
        $model->load([
            'adminUsers',
            'adminUsers.adminUserDepartments',
            'adminUsers.adminUserRoles'
        ]);
        $model->append(['statusDesc']);
        foreach ($model as &$item) {
            $adminDepartments = Arr::get($item, 'adminUsers.0.adminUserDepartments');
            $adminRoles = Arr::get($item, 'adminUsers.0.adminUserRoles');
            $department = [];
            foreach ($adminDepartments as $adminDepartment) {
                $department[] = [
                    'id' => Arr::get($adminDepartment, 'admin_department_id'),
                    'administrator' => Arr::get($adminDepartment, 'administrator')
                ];
            }
            $item->admin_departments = $department;
            $item->admin_role_ids = Arr::pluck($adminRoles, 'id');
        }
        return $this->success($model->toArray());
    }

    /**
     * @return JsonResponse
     * @throws MessageException
     */
    public function store()
    {
        $this->addParam('customer_id', Access::getCustomerId());
        $validator = Validator::make($this->getParam(), [
            'mobile' => [
                Rule::unique(AdminUser::class)
                    ->withoutTrashed(),
            ],
        ], [
            'mobile.unique' => '员工手机号不能重复',
        ]);
        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }
        $id = AdminUserAdd::init()
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
     * @throws MessageException
     */
    public function update()
    {
        $validator = Validator::make($this->getParam(), [
            'id' => [
                'required',
                Rule::exists(AdminUser::class)
                    ->withoutTrashed(),
            ],
            'mobile' => [
                Rule::unique(AdminUser::class)
                    ->withoutTrashed()
                    ->ignore($this->getParam('id'))
            ],
        ], [
            'id.required' => '请选择员工',
            'id.exists' => '员工id不存在',
            'mobile.unique' => '员工手机号不能重复',
        ]);
        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }
        $id = AdminUserUpdate::init()
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

    public function destroy()
    {
        $id = $this->getParam('id');
        if (is_array($id)) {
            foreach ($id as $value) {
                AdminUserDelete::init()
                    ->setId($value)
                    ->run()
                    ->getId();
            }
            return $this->success();
        }
        AdminUserDelete::init()
            ->setId($id)
            ->run()
            ->getId();
        return $this->success();
    }

    public function all()
    {
        $param = $this->getParam();
        $select = Arr::get($param, 'select', 'id as value, name as label');
        $model = AdminUser::query()
            ->selectRaw($select);
        $model = $this->filter($model);
        $model = $model->get();
        return $this->json($model);
    }

    public function allStatus()
    {
        $statusDesc = AdminUser::STATUS_DESC;
        $data = [];
        foreach ($statusDesc as $value => $label) {
            $data[] = compact('value', 'label');
        }
        return $this->json($data);
    }

    public function allSex()
    {
        $statusDesc = AdminUser::SEX_DESC;
        Arr::forget($statusDesc, [AdminUser::SEX_UNKNOWN]);
        $data = [];
        foreach ($statusDesc as $value => $label) {
            $data[] = compact('value', 'label');
        }
        return $this->json($data);
    }

    public function pagePermission()
    {
        $param = $this->getParam();
        $id = Arr::get($param, 'admin_user_id');
        $adminMenuIds = AdminMenuIdsByAdminUserIdGet::init()
            ->setAdminUserId($id)
            ->run()
            ->getAdminMenuIds();
        $adminPageColumnIds = AdminPageColumnIdsByAdminUserIdGet::init()
            ->setAdminUserId($id)
            ->run()
            ->getAdminPageColumnIds();
        $adminPageOptionIds = AdminPageOptionIdsByAdminUserIdGet::init()
            ->setAdminUserId($id)
            ->run()
            ->getAdminPageOptionIds();
        return $this->success(compact('adminMenuIds', 'adminPageOptionIds', 'adminPageColumnIds'));
    }

    public function requestPermission()
    {
        $param = $this->getParam();
        $id = Arr::get($param, 'admin_user_id');
        $adminRequests = AdminRequestsByAdminUserIdGet::init()
            ->setAdminUserId($id)
            ->run()
            ->getAdminRequests();
        return $this->success(compact('adminRequests'));
    }
}
