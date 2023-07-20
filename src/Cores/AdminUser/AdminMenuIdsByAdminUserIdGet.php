<?php

namespace Qz\Admin\Permission\Cores\AdminUser;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminMenu;
use Qz\Admin\Permission\Models\AdminUserDepartment;
use Qz\Admin\Permission\Models\AdminUserRole;

class AdminMenuIdsByAdminUserIdGet extends Core
{
    protected function execute()
    {
        $model = AdminUserRole::query()
            ->where('admin_user_id', $this->getAdminUserId())
            ->get();
        $model->load([
            'adminRole',
            'adminRole.adminRoleMenus'
        ]);
        foreach ($model as $value) {
            $adminRole = $value->adminRole;
            if (empty($adminRole)) {
                continue;
            }
            $adminRoleMenus = $adminRole->adminRoleMenus;
            foreach ($adminRoleMenus as $adminRoleMenu) {
                $this->adminMenuIds[] = $adminRoleMenu->admin_menu_id;
            }
        }
        $adminDepartmentIds = AdminUserDepartment::query()
            ->where('admin_user_id', $this->getAdminUserId())
            ->where('administrator', true)
            ->pluck('admin_department_id')
            ->toArray();
        if (!empty($adminDepartmentIds)) {
            $addAdminMenuIds = AdminMenu::query()
                ->where('path', '/admin-department')
                ->OrWhere('name', '系统设置')
                ->pluck('id')
                ->toArray();
            foreach ($addAdminMenuIds as $addAdminMenuId) {
                $this->adminMenuIds[] = $addAdminMenuId;
            }
        }
        $this->setAdminMenuIds(array_unique((array) $this->adminMenuIds));
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
     * @return AdminMenuIdsByAdminUserIdGet
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
