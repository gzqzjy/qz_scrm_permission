<?php

namespace Qz\Admin\Permission\Cores\AdminDepartment;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminCategoryDepartment;
use Qz\Admin\Permission\Models\AdminDepartmentRole;
use Qz\Admin\Permission\Models\AdminUserDepartment;
use Qz\Admin\Permission\Models\AdminUserRole;

class GetInfoByAdminUserId extends Core
{
    protected function execute()
    {

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
     * @return GetAdminDepartmentIdsByAdminUserId
     */
    public function setAdminUserId($adminUserId)
    {
        $this->adminUserId = $adminUserId;
        $adminDepartmentIds = AdminUserDepartment::query()
            ->where('admin_user_id', $adminUserId)
            ->pluck('admin_department_id')
            ->toArray();
        $this->setAdminDepartmentIds($adminDepartmentIds);
        $adminUserRoleIds = AdminUserRole::query()
            ->where('admin_user_id', $adminUserId)
            ->pluck('admin_role_id')
            ->toArray();
        $this->setAdminUserRoleIds($adminUserRoleIds);
        return $this;
    }

    protected $adminUserIds;

    /**
     * @return mixed
     */
    public function getAdminUserIds()
    {
        return $this->adminUserIds;
    }

    /**
     * @param mixed $adminUserIds
     * @return GetInfoByAdminUserId
     */
    public function setAdminUserIds($adminUserIds)
    {
        $this->adminUserIds = $adminUserIds;
        $adminDepartmentIds = AdminUserDepartment::query()
            ->whereIn('admin_user_id', $adminUserIds)
            ->pluck('admin_department_id')
            ->toArray();
        $this->setAdminDepartmentIds($adminDepartmentIds);
        $adminUserRoleIds = AdminUserRole::query()
            ->whereIn('admin_user_id', $adminUserIds)
            ->pluck('admin_role_id')
            ->toArray();
        $this->setAdminUserRoleIds($adminUserRoleIds);
        return $this;
    }

    protected $adminDepartmentIds;

    /**
     * @return mixed
     */
    public function getAdminDepartmentIds()
    {
        return $this->adminDepartmentIds;
    }

    /**
     * @param mixed $adminDepartmentIds
     * @return GetAdminDepartmentIdsByAdminUserId
     */
    public function setAdminDepartmentIds($adminDepartmentIds)
    {
        $this->adminDepartmentIds = $adminDepartmentIds;
        return $this;
    }

    protected $adminDepartmentRoleIds;

    /**
     * @return mixed
     */
    public function getAdminDepartmentRoleIds()
    {
        if ($this->getAdminDepartmentIds()){
            $adminRoleIds = AdminDepartmentRole::query()
                ->whereIn('admin_department_id', $this->getAdminDepartmentIds())
                ->pluck('admin_role_id')
                ->toArray();
            $this->setAdminDepartmentRoleIds($adminRoleIds);
        }
        return $this->adminDepartmentRoleIds;
    }

    /**
     * @param mixed $adminDepartmentRoleIds
     * @return GetInfoByAdminUserId
     */
    protected function setAdminDepartmentRoleIds($adminDepartmentRoleIds)
    {
        $this->adminDepartmentRoleIds = $adminDepartmentRoleIds;
        return $this;
    }

    protected $adminUserRoleIds;

    /**
     * @return mixed
     */
    public function getAdminUserRoleIds()
    {
        return $this->adminUserRoleIds;
    }

    /**
     * @param mixed $adminUserRoleIds
     * @return GetInfoByAdminUserId
     */
    protected function setAdminUserRoleIds($adminUserRoleIds)
    {
        $this->adminUserRoleIds = $adminUserRoleIds;
        return $this;
    }

    protected $categoryIds;

    /**
     * @return mixed
     */
    public function getCategoryIds()
    {
        if ($this->getAdminDepartmentIds()){
            $categoryIds = AdminCategoryDepartment::query()
                ->whereIn('admin_department_id', $this->getAdminDepartmentIds())
                ->pluck('category_id')
                ->toArray();
            $this->setCategoryIds($categoryIds);
        }
        return $this->categoryIds;
    }

    /**
     * @param mixed $categoryIds
     * @return GetCategoryIdsByAdminUserId
     */
    protected function setCategoryIds($categoryIds)
    {
        $this->categoryIds = $categoryIds;
        return $this;
    }
}
