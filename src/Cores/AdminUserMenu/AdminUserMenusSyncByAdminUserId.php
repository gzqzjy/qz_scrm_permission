<?php

namespace Qz\Admin\Permission\Cores\AdminUserMenu;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserMenu;
use Illuminate\Support\Arr;

class AdminUserMenusSyncByAdminUserId extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminUserId())) {
            return;
        }
        $adminUserMenus = $this->getAdminUserMenus();
        if (is_null($adminUserMenus)) {
            return;
        }
        $deletes = AdminUserMenu::query()
            ->select('id')
            ->where('admin_user_id', $this->getAdminUserId())
            ->whereNotIn('admin_menu_id', Arr::pluck($adminUserMenus, $this->getAdminMenuIdKey()))
            ->get();
        foreach ($deletes as $delete) {
            AdminUserMenuDelete::init()
                ->setId(Arr::get($delete, 'id'))
                ->run();
        }
        if (!empty($adminUserMenus)) {
            foreach ($adminUserMenus as $adminUserMenu) {
                AdminUserMenuAdd::init()
                    ->setAdminMenuId(Arr::get($adminUserMenu, $this->getAdminMenuIdKey()))
                    ->setType(Arr::get($adminUserMenu, 'type'))
                    ->setAdminUserId($this->getAdminUserId())
                    ->run();
            }
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

    protected $adminUserMenus;

    /**
     * @return mixed
     */
    public function getAdminUserMenus()
    {
        return $this->adminUserMenus;
    }

    /**
     * @param mixed $adminUserMenus
     * @return AdminUserMenusSyncByAdminUserId
     */
    public function setAdminUserMenus($adminUserMenus)
    {
        $this->adminUserMenus = $adminUserMenus;
        return $this;
    }

    protected $adminMenus;

    /**
     * @return mixed
     */
    public function getAdminMenus()
    {
        return $this->adminMenus;
    }

    /**
     * @param mixed $adminMenus
     * @return AdminUserMenusSyncByAdminUserId
     */
    public function setAdminMenus($adminMenus)
    {
        $this->adminMenus = $adminMenus;
        return $this;
    }

    protected $adminMenuIdKey = 'admin_menu_id';

    /**
     * @return string
     */
    public function getAdminMenuIdKey()
    {
        return $this->adminMenuIdKey;
    }

    /**
     * @param string $adminMenuIdKey
     * @return AdminUserMenusSyncByAdminUserId
     */
    public function setAdminMenuIdKey($adminMenuIdKey)
    {
        $this->adminMenuIdKey = $adminMenuIdKey;
        return $this;
    }
}
