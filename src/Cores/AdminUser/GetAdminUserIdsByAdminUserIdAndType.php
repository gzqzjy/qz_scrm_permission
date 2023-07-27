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
        if (empty($this->getAdminUserId()) || empty($this->getDepartmentType())) {
            return;
        }
        $adminDepartmentIds = GetInfoByAdminUserId::init()
            ->setAdminUserId($this->getAdminUserId())
            ->getAdminDepartmentIds();

        if (empty($adminDepartmentIds)) {
            return;
        }
        $types = $this->getDepartmentType();
        if (!is_array($this->getDepartmentType())) {
            $types = explode(AdminRoleRequest::CHARACTER, $this->getDepartmentType());
        }
        foreach ($types as $type) {
            $this->getAdminUserByTypeAndDepartment($type, $adminDepartmentIds);
        }
    }

    protected function getAdminUserByTypeAndDepartment($type, $adminDepartmentIds)
    {
        switch ($type) {
            case AdminRoleRequest::SELF:
                $this->addAdminUserId($this->getAdminUserId());
                break;
            case AdminRoleRequest::UNDEFINED:
                $this->addAdminUserId(0);
                break;
            case AdminRoleRequest::THIS:
                $adminUserIds = AdminUserDepartment::query()
                    ->where('admin_user_id', '!=', $this->getAdminUserId())
                    ->whereIn('admin_department_id', $adminDepartmentIds)
                    ->pluck('admin_user_id')
                    ->toArray();
                $this->addAdminUserIds($adminUserIds);
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
                            ->where('admin_user_id', '!=', $this->getAdminUserId())
                            ->whereIn('admin_department_id', $adminDepartmentIds)
                            ->pluck('admin_user_id')
                            ->toArray();
                        $this->addAdminUserIds($adminUserIds);
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
                $this->addAdminUserIds($adminUserIds);
                break;
        }
        return;
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
     * @return GetAdminUserIdsByAdminUserIdAndType
     */
    public function setAdminUserId($adminUserId)
    {
        $this->adminUserId = $adminUserId;
        return $this;
    }

    protected $adminUserIds;

    /**
     * @return mixed
     */
    public function getAdminUserIds()
    {
        return is_null($this->adminUserIds) ? [] : $this->adminUserIds;
    }

    /**
     * @param mixed $adminUserIds
     * @return GetAdminUserIdsByAdminUserIdAndType
     */
    public function setAdminUserIds($adminUserIds)
    {
        $this->adminUserIds = $adminUserIds;
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

    protected function addAdminUserIds($adminUserIds)
    {
        $this->setAdminUserIds(array_merge((array) $this->adminUserIds, $adminUserIds));
    }

    protected function addAdminUserId($adminUserId)
    {
        $adminUserIds = (array) $this->adminUserIds;
        $this->setAdminUserIds(Arr::prepend($adminUserIds, $adminUserId));
    }
}
