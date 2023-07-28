<?php

namespace Qz\Admin\Permission\Cores\AdminUser;

use Illuminate\Support\Arr;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUser;
use Qz\Admin\Permission\Models\AdminUserMenu;

class AdminMenuIdsByAdminUserIdGet extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminUserId())) {
            return;
        }
        $model = AdminUser::query()
            ->select(['id'])
            ->find($this->getAdminUserId());
        if (empty($model)) {
            return;
        }
        $model->load([
            'adminUserRoles',
            'adminUserRoles.adminRole',
            'adminUserRoles.adminRole.adminRoleMenus',
            'adminUserMenus',
        ]);
        $adminUserRoles = Arr::get($model, 'adminUserRoles');
        foreach ($adminUserRoles as $adminUserRole) {
            $adminRole = Arr::get($adminUserRole, 'adminRole');
            if (empty($adminRole)) {
                continue;
            }
            $adminRoleMenus = Arr::get($adminRole, 'adminRoleMenus');
            foreach ($adminRoleMenus as $adminRoleMenu) {
                $this->adminMenuIds[] = Arr::get($adminRoleMenu, 'admin_menu_id');
            }
        }
        $adminUserMenus = Arr::get($model, 'adminUserMenus');
        foreach ($adminUserMenus as $adminUserMenu) {
            if (Arr::get($adminUserMenu, 'type') != AdminUserMenu::TYPE_DELETE) {
                $this->adminMenuIds[] = Arr::get($adminUserMenu, 'admin_menu_id');
            } else {
                $this->adminMenuIds = Arr::where($this->adminMenuIds, function ($adminMenuId) use ($adminUserMenu) {
                    return $adminMenuId != Arr::get($adminUserMenu, 'admin_menu_id');
                });
            }
        }
        $this->adminMenuIds = array_unique(array_values($this->adminMenuIds));
    }

    protected $adminMenuIds = [];

    /**
     * @return mixed
     */
    public function getAdminMenuIds()
    {
        return $this->adminMenuIds;
    }

    /**
     * @param mixed $adminMenuIds
     * @return $this
     */
    public function setAdminMenuIds($adminMenuIds)
    {
        $this->adminMenuIds = $adminMenuIds;
        return $this;
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
     * @param mixed $adminUserId
     * @return AdminMenuIdsByAdminUserIdGet
     */
    public function setAdminUserId($adminUserId)
    {
        $this->adminUserId = $adminUserId;
        return $this;
    }
}
