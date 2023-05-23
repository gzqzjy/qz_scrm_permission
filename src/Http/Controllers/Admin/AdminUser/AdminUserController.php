<?php

namespace Qz\Admin\Permission\Http\Controllers\Admin\AdminUser;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
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
use Qz\Admin\Permission\Models\AdminPage;

class AdminUserController extends AdminController
{
    public function get()
    {
        $model = AdminUser::query()
            ->whereHas('adminUserCustomerSubsystems', function (Builder $builder) {
                $builder->whereHas('customerSubsystem', function (Builder $builder) {
                    $builder->where('subsystem_id', Access::getSubsystemId());
                });
            });
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
            ],
            'mobile' => [
                'required',
                Rule::unique(AdminUser::class)
                    ->withoutTrashed(),
            ],
        ], [
            'name.required' => '员工名不能为空',
            'mobile.required' => '员工路由不能为空',
            'mobile.unique' => '员工路由不能重复',
        ]);
        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }
        $this->addParam('customer_subsystem_id', Access::getCustomerSubsystemId());
        $id = AdminUserAdd::init()
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
            'mobile' => [
                Rule::unique(AdminUser::class)
                    ->withoutTrashed()
                    ->where('subsystem_id', Access::getSubsystemId())
                    ->ignore($this->getParam('id'))
            ],
        ], [
            'mobile.unique' => '员工路由不能重复',
        ]);
        if ($validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }
        $id = AdminUserUpdate::init()
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
            ->whereHas('adminUserCustomerSubsystems', function (Builder $builder) {
                $builder->whereHas('customerSubsystem', function (Builder $builder) {
                    $builder->where('subsystem_id', Access::getSubsystemId());
                });
            })
            ->selectRaw($select);
        $model = $this->filter($model);
        $model = $model->get();
        return $this->response($model);
    }
}
