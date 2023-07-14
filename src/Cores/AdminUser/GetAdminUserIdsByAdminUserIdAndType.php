<?php

namespace Qz\Admin\Permission\Cores\AdminUser;

use Illuminate\Support\Arr;
use Qz\Admin\Permission\Cores\AdminDepartment\GetInfoByAdminUserId;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminDepartment;
use Qz\Admin\Permission\Models\AdminRoleRequest;
use Qz\Admin\Permission\Models\AdminUserDepartment;

class GetAdminUserIdsByAdminUserIdAndType extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminUserCustomerSubSystemId()) || empty($this->getDepartmentType())) {
            return;
        }
        $adminDepartmentIds = GetInfoByAdminUserId::init()
            ->setAdminUserId($this->getAdminUserCustomerSubSystemId())
            ->getAdminDepartmentIds();

        if (empty($adminDepartmentIds)) {
            return;
        }
        $types = $this->getDepartmentType();
        if (!is_array($this->getDepartmentType())) {
            $types = explode(AdminRoleRequest::CHARACTER, $this->getDepartmentType());
        }
        foreach ($types as $type) {
            $this->getAdminUserCustomerSystemByTypeAndDepartment($type, $adminDepartmentIds);
        }
    }

    protected function getAdminUserCustomerSystemByTypeAndDepartment($type, $adminDepartmentIds)
    {
        switch ($type) {
            case AdminRoleRequest::SELF:
                $this->addAdminUserCustomerSubSystemId($this->getAdminUserCustomerSubSystemId());
                break;
            case AdminRoleRequest::UNDEFINED:
                $this->addAdminUserCustomerSubSystemId(0);
                break;
            case AdminRoleRequest::THIS:
                $adminUserIds = AdminUserDepartment::query()
                    ->where('admin_user_id', '!=', $this->getAdminUserCustomerSubSystemId())
                    ->whereIn('admin_department_id', $adminDepartmentIds)
                    ->pluck('admin_user_id')
                    ->toArray();
                $this->addAdminUserCustomerSubSystemIds($adminUserIds);
                break;
            case AdminRoleRequest::PEER:
                //同级部门 同个上级的其他部门及其他部门的下级员工数据
                $pidAdminDepartmentIds = AdminDepartment::query()
                    ->whereIn('id', $adminDepartmentIds)
                    ->pluck('pid')
                    ->toArray();
                if ($pidAdminDepartmentIds) {
                    $adminDepartmentIds = AdminDepartment::query()
                        ->whereIn('pid', $pidAdminDepartmentIds)
                        ->whereNotIn('id', $adminDepartmentIds)
                        ->pluck('id')
                        ->toArray();
                    if ($adminDepartmentIds) {
                        $adminDepartmentIds = GetSubAdminDepartmentIdsByAdminDepartmentIds::init()
                            ->setAdminDepartmentIds($adminDepartmentIds)
                            ->run()
                            ->getAllAdminDepartmentIds();
                        $adminUserIds = AdminUserDepartment::query()
                            ->where('admin_user_id', '!=', $this->getAdminUserCustomerSubSystemId())
                            ->whereIn('admin_department_id', $adminDepartmentIds)
                            ->pluck('admin_user_id')
                            ->toArray();
                        $this->addAdminUserCustomerSubSystemIds($adminUserIds);
                    }
                }
                break;
            case AdminRoleRequest::CHILDREN:
                $childrenDepartmentIds = GetSubAdminDepartmentIdsByAdminDepartmentIds::init()
                    ->setAdminDepartmentIds($adminDepartmentIds)
                    ->run()
                    ->getSubAdminDepartmentIds();
                if (empty($childrenDepartmentIds)) {
                    break;
                }
                $adminUserIds = AdminUserDepartment::query()
                    ->whereIn('admin_department_id', $childrenDepartmentIds)
                    ->pluck('admin_user_id')
                    ->toArray();
                $this->addAdminUserCustomerSubSystemIds($adminUserIds);
                break;
        }
        return;
    }

    protected $adminUserCustomerSubSystemId;

    /**
     * @return mixed
     */
    public function getAdminUserCustomerSubSystemId()
    {
        return $this->adminUserCustomerSubSystemId;
    }

    /**
     * @param mixed $adminUserCustomerSubSystemId
     * @return GetAdminUserIdsByAdminUserIdAndType
     */
    public function setAdminUserCustomerSubSystemId($adminUserCustomerSubSystemId)
    {
        $this->adminUserCustomerSubSystemId = $adminUserCustomerSubSystemId;
        return $this;
    }

    protected $adminUserCustomerSubSystemIds;

    /**
     * @return mixed
     */
    public function getAdminUserCustomerSubSystemIds()
    {
        return is_null($this->adminUserCustomerSubSystemIds) ? [] : $this->adminUserCustomerSubSystemIds;
    }

    /**
     * @param mixed $adminUserCustomerSubSystemIds
     * @return GetAdminUserIdsByAdminUserIdAndType
     */
    public function setAdminUserCustomerSubSystemIds($adminUserCustomerSubSystemIds)
    {
        $this->adminUserCustomerSubSystemIds = $adminUserCustomerSubSystemIds;
        return $this;
    }

    protected $departmentType;

    /**
     * @return mixed
     */
    public function getDepartmentType()
    {
        return $this->departmentType;
    }

    /**
     * @param mixed $departmentType
     * @return GetAdminUserIdsByAdminUserIdAndType
     */
    public function setDepartmentType($departmentType)
    {
        $this->departmentType = $departmentType;
        return $this;
    }

    protected function addAdminUserCustomerSubSystemIds($adminUserCustomerSubSystemIds)
    {
        $this->setAdminUserCustomerSubSystemIds(array_merge((array) $this->adminUserCustomerSubSystemIds, $adminUserCustomerSubSystemIds));
    }

    protected function addAdminUserCustomerSubSystemId($adminUserCustomerSubSystemId)
    {
        $adminUserCustomerSubSystemIds = (array) $this->adminUserCustomerSubSystemIds;
        $this->setAdminUserCustomerSubSystemIds(Arr::prepend($adminUserCustomerSubSystemIds, $adminUserCustomerSubSystemId));
    }
}
