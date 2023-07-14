<?php

namespace Qz\Admin\Permission\Cores\AdminUser;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Qz\Admin\Permission\Cores\AdminRole\GetMenuByAdminRole;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminMenu;
use Qz\Admin\Permission\Models\AdminUserRole;

class GetFeaturePermissionByAdminUserId extends Core
{
    protected $addAdminMenuIds = [];

    protected $addAdminPageColumnIds = [];

    protected $addAdminPageOptionIds = [];

    protected $deleteAdminMenuIds = [];

    protected $deleteAdminPageColumnIds = [];

    protected $deleteAdminPageOptionIds = [];

    protected function execute()
    {
        if (empty($this->getAdminUserId()) || empty($this->getFeaturePermission())) {
            return;
        }

        $adminRoleMenuIds = $adminRolePageColumnIds = $adminRolePageOptionIds = $adminMenus = $adminPageColumns = $adminPageOptions = [];
        $adminRoleIds = AdminUserRole::query()
            ->where('admin_user_id', $this->getAdminUserId())
            ->pluck('admin_role_id')
            ->toArray();
        if ($adminRoleIds) {
            $rolePermission = GetMenuByAdminRole::init()
                ->setAdminRoleIds($adminRoleIds);
            $adminRoleMenuIds = $rolePermission->getAdminMenuIds();
            $adminRolePageColumnIds = $rolePermission->getAdminPageColumnIds();
            $adminRolePageOptionIds = $rolePermission->getAdminPageOptionIds();
        }

        foreach ($this->getFeaturePermission() as $permission) {
            $this->getCheckPermission($permission);
        }
        $admin = AdminMenu::query()
            ->whereIn('id', $this->addAdminMenuIds)
            ->pluck('name')
            ->toArray();

        if ($this->addAdminMenuIds) {
            $adminMenuIds = array_diff($this->addAdminMenuIds, $adminRoleMenuIds);

            if ($adminMenuIds) {
                $adminMenus = array_map(function ($adminMenuId) {
                    return [
                        'id' => $adminMenuId,
                        'type' => 'add',
                    ];
                }, $adminMenuIds);
            }
        }

        if ($this->addAdminPageColumnIds) {
            $adminPageColumnIds = array_diff($this->addAdminPageColumnIds, $adminRolePageColumnIds);
            if ($adminPageColumnIds) {
                $adminPageColumns = array_map(function ($adminPageColumnId) {
                    return [
                        'id' => $adminPageColumnId,
                        'type' => 'add',
                    ];
                }, $adminPageColumnIds);
            }
        }
        if ($this->addAdminPageOptionIds) {
            $adminPageOptionIds = array_diff($this->addAdminPageOptionIds, $adminRolePageOptionIds);
            if ($adminPageOptionIds) {
                $adminPageOptions = array_map(function ($adminPageOptionId) {
                    return [
                        'id' => $adminPageOptionId,
                        'type' => 'add',
                    ];
                }, $adminPageOptionIds);
            }
        }
        if ($this->deleteAdminMenuIds) {
            $adminMenuIds = array_intersect($this->deleteAdminMenuIds, $adminRoleMenuIds);
            if ($adminMenuIds) {
                $adminMenus = array_merge(array_map(function ($adminMenuId) {
                    return [
                        'id' => $adminMenuId,
                        'type' => 'delete',
                    ];
                }, $adminMenuIds), $adminMenus);
            }
        }
        if ($this->deleteAdminPageColumnIds) {
            $adminPageColumnIds = array_intersect($this->deleteAdminPageColumnIds, $adminRolePageColumnIds);
            if ($adminPageColumnIds) {
                $adminPageColumns = array_merge(array_map(function ($adminPageColumnId) {
                    return [
                        'id' => $adminPageColumnId,
                        'type' => 'delete',
                    ];
                }, $adminPageColumnIds), $adminPageColumns);
            }
        }
        if ($this->deleteAdminPageOptionIds) {
            $adminPageOptionIds = array_intersect($this->deleteAdminPageOptionIds, $adminRolePageOptionIds);
            if ($adminPageOptionIds) {
                $adminPageOptions = array_merge(array_map(function ($adminPageOptionId) {
                    return [
                        'id' => $adminPageOptionId,
                        'type' => 'delete',
                    ];
                }, $adminPageOptionIds), $adminPageOptions);
            }
        }
        $this->setAdminMenus($adminMenus);
        $this->setAdminPageColumns($adminPageColumns);
        $this->setAdminPageOptions($adminPageOptions);
    }

    protected function getCheckPermission($permission)
    {
        Arr::get($permission, 'check') ? $this->addAdminMenuIds[] = Arr::get($permission, 'value') : $this->deleteAdminMenuIds[] = Arr::get($permission, 'value');

        if ($columns = Arr::get($permission, 'columns')) {
            foreach ($columns as $column) {
                $id = Str::replace("column_", "", Arr::get($column, 'value'));
                Arr::get($column, 'check') ? $this->addAdminPageColumnIds[] = $id : $this->deleteAdminPageColumnIds[] = $id;
            }
        }
        if ($options = Arr::get($permission, 'options')) {
            foreach ($options as $option) {
                $id = Str::replace("option_", "", Arr::get($option, 'value'));
                Arr::get($option, 'check') ? $this->addAdminPageOptionIds[] = $id : $this->deleteAdminPageOptionIds[] = $id;
            }
        }
        if ($children = Arr::get($permission, 'children')) {
            foreach ($children as $child) {
                $this->getCheckPermission($child);
            }
        }
    }

    protected $adminUserId;

    /**
     * @return mixed
     */
    protected function getAdminUserId()
    {
        return $this->adminUserId;
    }

    /**
     * @param mixed $adminUserId
     * @return GetFeaturePermissionByAdminUserId
     */
    public function setAdminUserId($adminUserId)
    {
        $this->adminUserId = $adminUserId;
        return $this;
    }

    protected $featurePermission;

    /**
     * @return mixed
     */
    protected function getFeaturePermission()
    {
        return $this->featurePermission;
    }

    /**
     * @param mixed $featurePermission
     * @return GetFeaturePermissionByAdminUserId
     */
    public function setFeaturePermission($featurePermission)
    {
        $this->featurePermission = $featurePermission;
        return $this;
    }

    protected $adminMenus;

    protected $adminPageOptions;

    protected $adminPageColumns;

    /**
     * @return mixed
     */
    public function getAdminMenus()
    {
        return $this->adminMenus;
    }

    /**
     * @param mixed $adminMenus
     * @return GetFeaturePermissionByAdminUserId
     */
    protected function setAdminMenus($adminMenus)
    {
        $this->adminMenus = $adminMenus;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAdminPageOptions()
    {
        return $this->adminPageOptions;
    }

    /**
     * @param mixed $adminPageOptions
     * @return GetFeaturePermissionByAdminUserId
     */
    protected function setAdminPageOptions($adminPageOptions)
    {
        $this->adminPageOptions = $adminPageOptions;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAdminPageColumns()
    {
        return $this->adminPageColumns;
    }

    /**
     * @param mixed $adminPageColumns
     * @return GetFeaturePermissionByAdminUserId
     */
    protected function setAdminPageColumns($adminPageColumns)
    {
        $this->adminPageColumns = $adminPageColumns;
        return $this;
    }
}
