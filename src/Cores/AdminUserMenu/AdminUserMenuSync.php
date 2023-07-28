<?php

namespace Qz\Admin\Permission\Cores\AdminUserMenu;

use Qz\Admin\Permission\Cores\AdminUser\AdminMenuIdsByAdminUserIdGet;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserMenu;

class AdminUserMenuSync extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminUserId())) {
            return;
        }
        $adminMenuIds = $this->getAdminMenuIds();
        if (is_null($adminMenuIds)) {
            return;
        }
        AdminUserMenu::query()
            ->where('admin_user_id', $this->getAdminUserId())
            ->whereNotIn('admin_menu_id', $adminMenuIds)
            ->delete();
        $oldAdminMenuIds = AdminMenuIdsByAdminUserIdGet::init()
            ->setAdminUserId($this->getAdminUserId())
            ->run()
            ->getAdminMenuIds();
        $addIds = array_diff($adminMenuIds, $oldAdminMenuIds);
        foreach ($addIds as $addId) {
            AdminUserMenuAdd::init()
                ->setAdminMenuId($addId)
                ->setAdminUserId($this->getAdminUserId())
                ->setType(AdminUserMenu::TYPE_ADD)
                ->run();
        }
        $deleteIds = array_diff($oldAdminMenuIds, $adminMenuIds);
        foreach ($deleteIds as $deleteId) {
            AdminUserMenuAdd::init()
                ->setAdminMenuId($deleteId)
                ->setAdminUserId($this->getAdminUserId())
                ->setType(AdminUserMenu::TYPE_DELETE)
                ->run();
        }
    }

    protected $adminUserId;

    /**
     * @return mixed
     */
    public function getAdminUserId()
    {
        return $this->adminUserId;
    }

    /**
     * @param $adminUserId
     * @return $this
     */
    public function setAdminUserId($adminUserId)
    {
        $this->adminUserId = $adminUserId;
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
     * @return AdminUserMenuSync
     */
    public function setAdminMenuIds($adminMenuIds)
    {
        $this->adminMenuIds = $adminMenuIds;
        return $this;
    }
}
