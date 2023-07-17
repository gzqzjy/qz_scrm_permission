<?php

namespace Qz\Admin\Permission\Cores\AdminUser;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Qz\Admin\Permission\Cores\AdminRole\GetMenuByAdminRole;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserMenu;
use Qz\Admin\Permission\Models\AdminUserPageColumn;
use Qz\Admin\Permission\Models\AdminUserPageOption;
use Qz\Admin\Permission\Models\AdminUserRole;

class GetPermissionByAdminUserId extends Core
{
    protected function execute()
    {
        $adminRoleIds = AdminUserRole::query()
            ->where('admin_user_id', $this->getAdminUserId())
            ->pluck('admin_role_id')
            ->toArray();
        $adminMenuIds = [];
        $adminPageColumnIds = [];
        $adminPageOptionIds = [];
        if ($adminRoleIds) {
            $rolePermission = GetMenuByAdminRole::init()
                ->setAdminRoleIds($adminRoleIds)
                ->run();
            $adminMenuIds = $rolePermission->getAdminMenuIds();
            $adminPageColumnIds = $rolePermission->getAdminPageColumnIds();
            $adminPageOptionIds = $rolePermission->getAdminPageOptionIds();
        }
        $adminUserMenuIds = AdminUserMenu::query()
            ->where('admin_user_id', $this->getAdminUserId())
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
        $adminUserPageColumnIds = AdminUserPageColumn::query()
            ->where('admin_user_id', $this->getAdminUserId())
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
        $adminUserPageOptionIds = AdminUserPageOption::query()
            ->where('admin_user_id', $this->getAdminUserId())
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

    protected $adminUserId;

    protected $adminMenuIds;

    protected $adminPageColumnIds;

    protected $adminPageOptionIds;

    /**
     * @return mixed
     */
    public function getAdminUserId()
    {
        return $this->adminUserId;
    }

    /**
     * @param mixed $adminUserId
     * @return GetPermissionByAdminUserId
     */
    public function setAdminUserId($adminUserId)
    {
        $this->adminUserId = $adminUserId;
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
     * @return GetPermissionByAdminUserId
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
     * @return GetPermissionByAdminUserId
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
     * @return GetPermissionByAdminUserId
     */
    protected function setAdminPageOptionIds($adminPageOptionIds)
    {
        $this->adminPageOptionIds = $adminPageOptionIds;
        return $this;
    }
}
