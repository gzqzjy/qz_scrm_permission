<?php

namespace Qz\Admin\Permission\Cores\Auth;

use Illuminate\Support\Arr;
use Qz\Admin\Permission\Cores\AdminUser\GetSubAdminDepartmentIdsByAdminDepartmentIds;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminDepartment;
use Qz\Admin\Permission\Models\AdminRoleRequest;
use Qz\Admin\Permission\Models\AdminUser;
use Qz\Admin\Permission\Models\AdminUserDepartment;
use Qz\Admin\Permission\Models\AdminUserRequest;
use Qz\Admin\Permission\Models\AdminUserRequestEmployee;

class AdminUserIdsGet extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminUserId()) || empty($this->getAdminRequestId())) {
            return;
        }
        $adminUser = AdminUser::query()
            ->select(['customer_id'])
            ->find($this->getAdminUserId());
        if (empty($adminUser)) {
            return;
        }
        // 接口授权用户
        $this->adminUserRequestEmployeeAdd($this->getAdminRequestId(), $this->getAdminUserId());
        // 接口权限
        $this->adminUserRequest($this->getAdminRequestId(), $this->getAdminUserId());
    }

    protected function adminUserRequest($adminRequestId, $adminUserId)
    {
        $adminUserRequest = AdminUserRequest::query()
            ->where('admin_user_id', $adminUserId)
            ->where('admin_request_id', $adminRequestId)
            ->first();
        if (empty($adminUserRequest) && $adminRequestId && $adminUserId) {
            $this->adminUserRequest(0, 0);
            return;
        }
        $type = Arr::get($adminUserRequest, 'type');
        if (empty($type)) {
            return;
        }
        $types = explode(AdminUserRequest::CHARACTER, $type);
        foreach ($types as $type) {
            $this->adminUserRequestByType($type);
        }
        return;
    }

    protected function adminUserRequestByType($type)
    {
        if (empty($type)) {
            return;
        }
        switch ($type) {
            case AdminUserRequest::SELF:
                $this->ids[] = $this->getAdminUserId();
                break;
            case AdminRoleRequest::UNDEFINED:
                $this->ids[] = 0;
                break;
            case AdminRoleRequest::THIS:
                $adminDepartmentIds = AdminUserDepartment::query()
                    ->where('admin_user_id', '=', $this->getAdminUserId())
                    ->pluck('admin_department_id')
                    ->toArray();
                $adminUserIds = AdminUserDepartment::query()
                    ->where('admin_user_id', '!=', $this->getAdminUserId())
                    ->whereIn('admin_department_id', $adminDepartmentIds)
                    ->pluck('admin_user_id')
                    ->toArray();
                if (!empty($adminUserIds)) {
                    $this->ids[] = array_merge($this->ids, $adminUserIds);
                }
                break;
            case AdminRoleRequest::CHILDREN:
                $adminDepartmentIds = AdminUserDepartment::query()
                    ->where('admin_user_id', '=', $this->getAdminUserId())
                    ->pluck('admin_department_id')
                    ->toArray();
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
                if (!empty($adminUserIds)) {
                    $this->ids[] = array_merge($this->ids, $adminUserIds);
                }
                break;
        }
    }

    protected function adminUserRequestEmployeeAdd($adminRequestId, $adminUserId)
    {
        // 授权用户
        $permissionAdminUserIds = AdminUserRequestEmployee::query()
            ->select(['permission_admin_user_id'])
            ->where('admin_user_id', $adminUserId)
            ->where('admin_request_id', $adminRequestId)
            ->where('type', AdminUserRequestEmployee::TYPE_ADD)
            ->pluck('permission_admin_user_id')
            ->toArray();
        if ($adminRequestId && $adminUserId && empty($permissionAdminUserIds)) {
            $this->adminUserRequestEmployeeAdd(0, 0);
            return;
        } else if (!empty($permissionAdminUserIds)) {
            $this->ids = array_merge($this->ids, $permissionAdminUserIds);
        }
        return;
    }

    protected function adminUserRequestEmployeeDelete($adminRequestId)
    {
        // 授权用户
        $permissionAdminUserIds = AdminUserRequestEmployee::query()
            ->select(['permission_admin_user_id'])
            ->where('admin_user_id', $this->getAdminUserId())
            ->where('admin_request_id', $adminRequestId)
            ->where('type', AdminUserRequestEmployee::TYPE_DELETE)
            ->pluck('permission_admin_user_id')
            ->toArray();
        if ($adminRequestId && empty($permissionAdminUserIds)) {
            $this->adminUserRequestEmployeeDelete(0);
            return;
        } else if (!empty($permissionAdminUserIds)) {
            $this->ids = array_diff(array_unique($this->ids), $permissionAdminUserIds);
        }
        return;
    }

    protected $ids = [];

    /**
     * @return array
     */
    public function getIds()
    {
        return $this->ids;
    }

    /**
     * @param array $ids
     * @return AdminUserIdsGet
     */
    public function setIds($ids)
    {
        $this->ids = $ids;
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
     * @return AdminUserIdsGet
     */
    public function setAdminUserId($adminUserId)
    {
        $this->adminUserId = $adminUserId;
        return $this;
    }

    protected $adminRequestId;

    /**
     * @return mixed
     */
    public function getAdminRequestId()
    {
        return $this->adminRequestId;
    }

    /**
     * @param mixed $adminRequestId
     * @return AdminUserIdsGet
     */
    public function setAdminRequestId($adminRequestId)
    {
        $this->adminRequestId = $adminRequestId;
        return $this;
    }
}
