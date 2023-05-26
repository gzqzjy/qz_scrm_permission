<?php

namespace Qz\Admin\Permission\Cores\AdminUserCustomerSubsystemMenu;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystemMenu;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AdminUserCustomerSubsystemMenuSync extends Core
{
    protected function execute()
    {
        $adminMenuIds = $this->getAdminMenuIds();
        $deletes = AdminUserCustomerSubsystemMenu::query()
            ->select('id')
            ->where('admin_user_customer_subsystem_id', $this->getAdminUserCustomerSubsystemId())
            ->whereNotIn('admin_menu_id', $adminMenuIds)
            ->get();
        foreach ($deletes as $delete) {
            AdminUserCustomerSubsystemMenuDelete::init()
                ->setId()
                ->run(Arr::get($delete, 'id'));
        }
        foreach ($adminMenuIds as $adminMenuId) {
            AdminUserCustomerSubsystemMenuAdd::init()
                ->setAdminMenuId($adminMenuId)
                ->setAdminUserCustomerSubsystemId($this->getAdminUserCustomerSubsystemId())
                ->run();
        }
    }

    protected $adminUserCustomerSubsystemId;

    /**
     * @return mixed
     */
    public function getAdminUserCustomerSubsystemId()
    {
        return $this->adminUserCustomerSubsystemId;
    }

    /**
     * @param mixed $adminUserCustomerSubsystemId
     * @return AdminUserCustomerSubsystemMenuAdd
     */
    public function setAdminUserCustomerSubsystemId($adminUserCustomerSubsystemId)
    {
        $this->adminUserCustomerSubsystemId = $adminUserCustomerSubsystemId;
        return $this;
    }

    protected $adminMenuIds;

    /**
     * @return mixed
     */
    public function getAdminMenuIds()
    {
        return $this->adminMenuIds;
    }

    /**
     * @param mixed $adminMenuIds
     * @return AdminUserCustomerSubsystemMenuSync
     */
    public function setAdminMenuIds($adminMenuIds)
    {
        $this->adminMenuIds = $adminMenuIds;
        return $this;
    }
}
