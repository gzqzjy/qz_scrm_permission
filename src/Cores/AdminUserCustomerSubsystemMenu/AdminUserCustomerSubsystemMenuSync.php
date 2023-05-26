<?php

namespace Qz\Admin\Permission\Cores\AdminUserCustomerSubsystemMenu;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystemMenu;
use Illuminate\Support\Arr;

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
                ->setId(Arr::get($delete, 'id'))
                ->run();
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
     * @param $adminUserCustomerSubsystemId
     * @return $this
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
