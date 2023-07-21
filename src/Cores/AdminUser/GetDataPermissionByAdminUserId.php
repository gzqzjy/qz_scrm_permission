<?php

namespace Qz\Admin\Permission\Cores\AdminUser;

use Dflydev\DotAccessData\Data;
use Illuminate\Support\Arr;
use Qz\Admin\Permission\Cores\AdminDepartment\GetInfoByAdminUserId;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminRoleRequest;
use Qz\Admin\Permission\Models\AdminUserRequest;

class GetDataPermissionByAdminUserId extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminUserId()) || empty($this->getDataPermission())) {
            return;
        }
        $permissions = $this->getDataPermission();
        $adminUserId = $this->getAdminUserId();
        $adminRoleIds = GetInfoByAdminUserId::init()
            ->setAdminUserId($adminUserId)
            ->getAdminUserRoleIds();
        $adminRoleRequests = AdminRoleRequest::query()
            ->whereIn('admin_role_id', $adminRoleIds)
            ->get()
            ->groupBy('admin_request_id')
            ->toArray();

        $adminUserRequestDepartments = [];
        $adminUserRequestEmployees = [];

        foreach ($permissions as $permission) {
            $adminRequestId = Arr::get($permission, 'admin_request_id');
            if (!Arr::exists($permission, 'admin_request_id')) {
                return [];
            }
            $actions = Arr::get($permission, 'actions');
//            if (empty($actions)){
//                return [];
//            }
            if ($adminRoleRequest = Arr::get($adminRoleRequests, $adminRequestId)) {
                $adminRoleRequestType = [];
                foreach ($adminRoleRequest as $item) {
                    $adminRoleRequestType = array_merge($adminRoleRequestType, explode(AdminRoleRequest::CHARACTER, Arr::get($item, 'type')));
                }
                $adminRoleRequestType = array_values(array_unique($adminRoleRequestType));
                if ($adminRoleRequestType != $actions) {
                    $adminUserRequestDepartments[] = [
                        "admin_request_id" => $adminRequestId,
                        "type" => implode(AdminUserRequest::CHARACTER, $actions)
                    ];
                }
            } else {
                $adminUserRequestDepartments[] = [
                    "admin_request_id" => $adminRequestId,
                    "type" => implode(AdminUserRequest::CHARACTER, $actions)
                ];
            }
            $actions = array_diff($actions, [AdminUserRequest::UNDEFINED]);//排除其他 不返回0
            $adminUserIds = GetAdminUserIdsByAdminUserIdAndType::init()
                ->setAdminUserId($adminUserId)
                ->setDepartmentType($actions)
                ->run()
                ->getAdminUserIds();

            $adminUsers = Arr::get($permission, 'admin_users', []);
            $deleteAdminUserIds = array_diff($adminUserIds, $adminUsers);
            $addAdminUserIds = array_diff($adminUsers, $adminUserIds);
            if ($deleteAdminUserIds) {
                $delete = array_map(function ($value) use ($adminRequestId) {
                    return [
                        "admin_request_id" => $adminRequestId,
                        "admin_user_id" => $value,
                        "type" => "delete"
                    ];
                }, $deleteAdminUserIds);
                $adminUserRequestEmployees = array_merge($adminUserRequestEmployees, $delete);
            }
            if ($addAdminUserIds) {
                $add = array_map(function ($value) use ($adminRequestId) {
                    return [
                        "admin_request_id" => $adminRequestId,
                        "admin_user_id" => $value,
                        "type" => "add"
                    ];
                }, $addAdminUserIds);
                $adminUserRequestEmployees = array_merge($adminUserRequestEmployees, $add);
            }
        }
        $this->setAdminUserRequests($adminUserRequestDepartments);
        $this->setAdminUserRequestEmployees($adminUserRequestEmployees);
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
     * @return GetDataPermissionByAdminUserId
     */
    public function setAdminUserId($adminUserId)
    {
        $this->adminUserId = $adminUserId;
        return $this;
    }

    protected $dataPermission;

    /**
     * @return mixed
     */
    protected function getDataPermission()
    {
        return $this->dataPermission;
    }

    /**
     * @param mixed $dataPermission
     * @return GetDataPermissionByAdminUserId
     */
    public function setDataPermission($dataPermission)
    {
        $this->dataPermission = $dataPermission;
        return $this;
    }

    protected $adminUserRequestDepartments;

    /**
     * @return mixed
     */
    public function getAdminUserRequests()
    {
        return $this->adminUserRequestDepartments;
    }

    /**
     * @param mixed $adminUserRequestDepartments
     * @return GetDataPermissionByAdminUserId
     */
    protected function setAdminUserRequests($adminUserRequestDepartments)
    {
        $this->adminUserRequestDepartments = $adminUserRequestDepartments;
        return $this;
    }

    protected $adminUserRequestEmployees;

    /**
     * @return mixed
     */
    public function getAdminUserRequestEmployees()
    {
        return $this->adminUserRequestEmployees;
    }

    /**
     * @param mixed $adminUserRequestEmployees
     * @return GetDataPermissionByAdminUserId
     */
    protected function setAdminUserRequestEmployees($adminUserRequestEmployees)
    {
        $this->adminUserRequestEmployees = $adminUserRequestEmployees;
        return $this;
    }
}
