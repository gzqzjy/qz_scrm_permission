<?php

namespace Qz\Admin\Access\Http\Controllers\Admin;

use  Qz\Admin\Access\Cores\AdminPage\AdminPageAdd;
use  Qz\Admin\Access\Cores\AdminPage\AdminPageIdGet;
use  Qz\Admin\Access\Cores\AdminPageColumn\AdminPageColumnAdd;
use  Qz\Admin\Access\Cores\AdminPageOption\AdminPageOptionAdd;
use  Qz\Admin\Access\Facades\Access;
use  Qz\Admin\Access\Models\AdminPageColumn;
use  Qz\Admin\Access\Models\AdminUserCustomerSubsystemPageOption;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class AccessController extends AdminController
{
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

    public function login()
    {

    }

    public function layout()
    {

    }

    public function user()
    {

    }
}
