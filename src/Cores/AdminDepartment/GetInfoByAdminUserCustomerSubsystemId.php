<?php


namespace Qz\Admin\Permission\Cores\AdminDepartment;


use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminCategoryDepartment;
use Qz\Admin\Permission\Models\AdminDepartmentRole;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystemDepartment;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystemRole;

class GetInfoByAdminUserCustomerSubsystemId extends Core
{
    protected function execute()
    {

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
     * @return GetAdminDepartmentIdsByAdminUserCustomerSubsystemId
     */
    public function setAdminUserCustomerSubsystemId($adminUserCustomerSubsystemId)
    {
        $this->adminUserCustomerSubsystemId = $adminUserCustomerSubsystemId;
        $adminDepartmentIds = AdminUserCustomerSubsystemDepartment::query()
            ->where('admin_user_customer_subsystem_id', $adminUserCustomerSubsystemId)
            ->pluck('admin_department_id')
            ->toArray();
        $this->setAdminDepartmentIds($adminDepartmentIds);
        $adminUserCustomerSubsystemRoleIds = AdminUserCustomerSubsystemRole::query()
            ->where('admin_user_customer_subsystem_id', $adminUserCustomerSubsystemId)
            ->pluck('admin_role_id')
            ->toArray();
        $this->setAdminUserCustomerSubsystemRoleIds($adminUserCustomerSubsystemRoleIds);
        return $this;
    }

    protected $adminUserCustomerSubsystemIds;

    /**
     * @return mixed
     */
    public function getAdminUserCustomerSubsystemIds()
    {
        return $this->adminUserCustomerSubsystemIds;
    }

    /**
     * @param mixed $adminUserCustomerSubsystemIds
     * @return GetInfoByAdminUserCustomerSubsystemId
     */
    public function setAdminUserCustomerSubsystemIds($adminUserCustomerSubsystemIds)
    {
        $this->adminUserCustomerSubsystemIds = $adminUserCustomerSubsystemIds;
        $adminDepartmentIds = AdminUserCustomerSubsystemDepartment::query()
            ->whereIn('admin_user_customer_subsystem_id', $adminUserCustomerSubsystemIds)
            ->pluck('admin_department_id')
            ->toArray();
        $this->setAdminDepartmentIds($adminDepartmentIds);
        $adminUserCustomerSubsystemRoleIds = AdminUserCustomerSubsystemRole::query()
            ->whereIn('admin_user_customer_subsystem_id', $adminUserCustomerSubsystemIds)
            ->pluck('admin_role_id')
            ->toArray();
        $this->setAdminUserCustomerSubsystemRoleIds($adminUserCustomerSubsystemRoleIds);
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
     * @return GetAdminDepartmentIdsByAdminUserCustomerSubsystemId
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
     * @return GetInfoByAdminUserCustomerSubsystemId
     */
    protected function setAdminDepartmentRoleIds($adminDepartmentRoleIds)
    {
        $this->adminDepartmentRoleIds = $adminDepartmentRoleIds;
        return $this;
    }

    protected $adminUserCustomerSubsystemRoleIds;

    /**
     * @return mixed
     */
    public function getAdminUserCustomerSubsystemRoleIds()
    {
        return $this->adminUserCustomerSubsystemRoleIds;
    }

    /**
     * @param mixed $adminUserCustomerSubsystemRoleIds
     * @return GetInfoByAdminUserCustomerSubsystemId
     */
    protected function setAdminUserCustomerSubsystemRoleIds($adminUserCustomerSubsystemRoleIds)
    {
        $this->adminUserCustomerSubsystemRoleIds = $adminUserCustomerSubsystemRoleIds;
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
     * @return GetCategoryIdsByAdminUserCustomerSubsystemId
     */
    protected function setCategoryIds($categoryIds)
    {
        $this->categoryIds = $categoryIds;
        return $this;
    }




}
