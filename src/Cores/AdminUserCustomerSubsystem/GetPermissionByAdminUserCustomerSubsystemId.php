<?php


namespace Qz\Admin\Permission\Cores\AdminUserCustomerSubsystem;


use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Qz\Admin\Permission\Cores\AdminRole\GetMenuByAdminRole;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystemMenu;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystemPageColumn;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystemPageOption;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystemRole;

class GetPermissionByAdminUserCustomerSubsystemId extends Core
{
    protected function execute()
    {
        Log::info("getAdminUserCustomerSubsystemId:",[$this->getAdminUserCustomerSubsystemId()]);
        $adminRoleIds = AdminUserCustomerSubsystemRole::query()
            ->where('admin_user_customer_subsystem_id', $this->getAdminUserCustomerSubsystemId())
            ->pluck('admin_role_id')
            ->toArray();
        $adminMenuIds = [];
        $adminPageColumnIds = [];
        $adminPageOptionIds = [];
        if ($adminRoleIds) {
            $rolePermission = GetMenuByAdminRole::init()
                ->setAdminRoleIds($adminRoleIds);
            $adminMenuIds = $rolePermission->getAdminMenuIds();
            $adminPageColumnIds = $rolePermission->getAdminPageColumnIds();
            $adminPageOptionIds = $rolePermission->getAdminPageOptionIds();
        }
        $adminUserMenuIds = AdminUserCustomerSubsystemMenu::query()
            ->where('admin_user_customer_subsystem_id', $this->getAdminUserCustomerSubsystemId())
            ->select(['type', 'admin_menu_id'])
            ->get();
        $adminUserMenuIds = $adminUserMenuIds->groupBy('type')->toArray();
        if ($adminMenuAdd = Arr::get($adminUserMenuIds, 'add')) {
            $adminMenuIds = array_unique(array_merge($adminMenuIds, Arr::pluck($adminMenuAdd, 'admin_menu_id')));
        }
        if ($adminMenuDelete = Arr::get($adminUserMenuIds, 'delete')) {
            $adminMenuIds = array_diff($adminMenuIds, Arr::pluck($adminMenuDelete, 'admin_menu_id'));
        }

        $this->setAdminMenuIds(array_values($adminMenuIds));
        $adminUserPageColumnIds = AdminUserCustomerSubsystemPageColumn::query()
            ->where('admin_user_customer_subsystem_id', $this->getAdminUserCustomerSubsystemId())
            ->select(['type', 'admin_page_column_id'])
            ->get();
        $adminUserPageColumnIds = $adminUserPageColumnIds->groupBy('type')->toArray();
        if ($adminPageColumnAdd = Arr::get($adminUserPageColumnIds, 'add')) {
            $adminPageColumnIds = array_unique(array_merge($adminPageColumnIds, Arr::pluck($adminPageColumnAdd, 'admin_page_column_id')));
        }
        if ($adminPageColumnDelete = Arr::get($adminUserPageColumnIds, 'delete')) {
            $adminPageColumnIds = array_diff($adminPageColumnIds, Arr::pluck($adminPageColumnDelete, 'admin_page_column_id'));
        }
        $this->setAdminPageColumnIds(array_values($adminPageColumnIds));
        $adminUserPageOptionIds = AdminUserCustomerSubsystemPageOption::query()
            ->where('admin_user_customer_subsystem_id', $this->getAdminUserCustomerSubsystemId())
            ->select(['type', 'admin_page_option_id'])
            ->get();
        $adminUserPageOptionIds = $adminUserPageOptionIds->groupBy('type')->toArray();
        if ($adminPageOptionAdd = Arr::get($adminUserPageOptionIds, 'add')) {
            $adminPageOptionIds = array_unique(array_merge($adminPageOptionIds, Arr::pluck($adminPageOptionAdd, 'admin_page_option_id')));
        }
        if ($adminPageOptionDelete = Arr::get($adminUserPageOptionIds, 'delete')) {
            $adminPageOptionIds = array_diff($adminPageOptionIds, Arr::pluck($adminPageOptionDelete, 'admin_page_option_id'));
        }
        $this->setAdminPageOptionIds(array_values($adminPageOptionIds));
    }

    protected $adminUserCustomerSubsystemId;

    protected $adminMenuIds;

    protected $adminPageColumnIds;

    protected $adminPageOptionIds;

    /**
     * @return mixed
     */
    public function getAdminUserCustomerSubsystemId()
    {
        return $this->adminUserCustomerSubsystemId;
    }

    /**
     * @param mixed $adminUserCustomerSubsystemId
     * @return GetPermissionByAdminUserCustomerSubsystemId
     */
    public function setAdminUserCustomerSubsystemId($adminUserCustomerSubsystemId)
    {
        $this->adminUserCustomerSubsystemId = $adminUserCustomerSubsystemId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAdminMenuIds()
    {
        return $this->adminMenuIds;
    }

    /**
     * @param mixed $adminMenuIds
     * @return GetPermissionByAdminUserCustomerSubsystemId
     */
    protected function setAdminMenuIds($adminMenuIds)
    {
        $this->adminMenuIds = $adminMenuIds;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAdminPageColumnIds()
    {
        return $this->adminPageColumnIds;
    }

    /**
     * @param mixed $adminPageColumnIds
     * @return GetPermissionByAdminUserCustomerSubsystemId
     */
    protected function setAdminPageColumnIds($adminPageColumnIds)
    {
        $this->adminPageColumnIds = $adminPageColumnIds;
        return $this;
    }

    public function getAdminPageOptionIds()
    {
        return $this->adminPageOptionIds;
    }

    /**
     * @param mixed $adminPageOptionIds
     * @return GetPermissionByAdminUserCustomerSubsystemId
     */
    protected function setAdminPageOptionIds($adminPageOptionIds)
    {
        $this->adminPageOptionIds = $adminPageOptionIds;
        return $this;
    }


}
