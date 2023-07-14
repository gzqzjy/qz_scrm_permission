<?php

namespace Qz\Admin\Permission\Cores\AdminUserMenu;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserMenu;
use Illuminate\Support\Arr;

class AdminUserMenuSync extends Core
{
    protected function execute()
    {
        $adminMenuIds = $this->getAdminMenuIds();
        $deletes = AdminUserMenu::query()
            ->select('id')
            ->where('admin_user_id', $this->getAdminUserId())
            ->whereNotIn('admin_menu_id', $adminMenuIds)
            ->get();
        foreach ($deletes as $delete) {
            AdminUserMenuDelete::init()
                ->setId(Arr::get($delete, 'id'))
                ->run();
        }
        foreach ($adminMenuIds as $adminMenuId) {
            AdminUserMenuAdd::init()
                ->setAdminMenuId($adminMenuId)
                ->setAdminUserId($this->getAdminUserId())
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
