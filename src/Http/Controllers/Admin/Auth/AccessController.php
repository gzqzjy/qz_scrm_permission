<?php

namespace Qz\Admin\Permission\Http\Controllers\Admin\Auth;

use  Qz\Admin\Permission\Cores\AdminPage\AdminPageAdd;
use  Qz\Admin\Permission\Cores\AdminPage\AdminPageIdGet;
use  Qz\Admin\Permission\Cores\AdminPageColumn\AdminPageColumnAdd;
use  Qz\Admin\Permission\Cores\AdminPageOption\AdminPageOptionAdd;
use Qz\Admin\Permission\Cores\Subsystem\SubsystemIdGet;
use  Qz\Admin\Permission\Facades\Access;
use Qz\Admin\Permission\Http\Controllers\Admin\AdminController;
use  Qz\Admin\Permission\Models\AdminPageColumn;
use Qz\Admin\Permission\Models\AdminUser;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystem;
use  Qz\Admin\Permission\Models\AdminUserCustomerSubsystemPageOption;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class AccessController extends AdminController
{
    public function login()
    {
        $status = 'error';
        $type = 'mobile';
        $mobile = $this->getParam('mobile');
        $token = '';
        $model = AdminUser::query()
            ->where('mobile', $mobile)
            ->where('status', AdminUser::STATUS_NORMAL)
            ->whereHas('adminUserCustomerSubsystem', function (Builder $builder) {
                $builder->where('status', AdminUserCustomerSubsystem::STATUS_NORMAL)
                    ->where('subsystem_id', SubsystemIdGet::init()
                        ->run()
                        ->getId());
            })
            ->first();
        if (empty($model)) {
            return $this->response(compact('token', 'status', 'type'));
        }
        if ($model instanceof AdminUser) {
            $model->tokens()->delete();
            $token = $model->createToken('admin_user')->plainTextToken;
            if ($token) {
                $status = 'ok';
            }
        }
        return $this->response(compact('token', 'status', 'type'));
    }

    public function addPage()
    {
        $pageId = AdminPageAdd::init()
            ->setName($this->getParam('page_name'))
            ->setCode($this->getParam('page_code'))
            ->run()
            ->getId();
        return $this->success(compact('pageId'));
    }

    public function columns()
    {
        $pageId = AdminPageIdGet::init()
            ->setCode($this->getParam('page_code'))
            ->run()
            ->getId();
        $columns = $this->getParam('columns');
        $pageColumnIds = [];
        foreach ($columns as $column) {
            $pageColumnId = AdminPageColumnAdd::init()
                ->setAdminPageId($pageId)
                ->setCode(Arr::get($column, 'data_index'))
                ->setName(Arr::get($column, 'title'))
                ->run()
                ->getId();
            Arr::prepend($pageColumnIds, $pageColumnId);
        }
        $codes = AdminPageColumn::query()
            ->whereHas('AdminUserCustomerSubsystemPageColumn', function (Builder $builder) use ($pageColumnIds) {
                $builder->where('admin_user_id', Auth::guard('admin')->id())
                    ->where('customer_id', Access::getCustomerId())
                    ->where('subsystem_id', Access::getSubsystemId())
                    ->whereIn('admin_page_column_id', $pageColumnIds);
            })->pluck('code');
        return $this->success(compact('codes'));
    }

    public function option()
    {
        $pageId = AdminPageIdGet::init()
            ->setCode($this->getParam('page_code'))
            ->run()
            ->getId();
        $pageOptionId = AdminPageOptionAdd::init()
            ->setAdminPageId($pageId)
            ->setCode($this->getParam('option_code'))
            ->setName($this->getParam('option_name'))
            ->run()
            ->getId();
        $access = AdminUserCustomerSubsystemPageOption::query()
            ->where('admin_user_id', Auth::guard('admin')->id())
            ->where('customer_id', Access::getCustomerId())
            ->where('subsystem_id', Access::getSubsystemId())
            ->whereIn('admin_page_option_id', $pageOptionId)
            ->exists();
        return $this->response(compact('access'));
    }

    public function layout()
    {

    }

    public function user()
    {

    }
}
