<?php

namespace Qz\Admin\Permission\Cores\AdminUserRequestEmployee;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserRequestEmployee;
use Illuminate\Support\Arr;

class AdminUserRequestEmployeesSyncByAdminUserId extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminUserId())) {
            return;
        }
        $adminUserRequestEmployees = $this->getAdminUserRequestEmployees();
        if (is_null($adminUserRequestEmployees)) {
            return;
        }
        $deletes = AdminUserRequestEmployee::query()
            ->select('id')
            ->where('admin_user_id', $this->getAdminUserId())
            ->get();
        foreach ($deletes as $delete) {
            AdminUserRequestEmployeeDelete::init()
                ->setId(Arr::get($delete, 'id'))
                ->run();
        }
        if (!empty($adminUserRequestEmployees)) {
            foreach ($adminUserRequestEmployees as $adminUserRequestEmployee) {
                AdminUserRequestEmployeeAdd::init()
                    ->setAdminRequestId(Arr::get($adminUserRequestEmployee, $this->getAdminRequestIdKey()))
                    ->setPermissionAdminUserId(Arr::get($adminUserRequestEmployee, $this->getPermissionAdminUserIdKey()))
                    ->setType(Arr::get($adminUserRequestEmployee, 'type'))
                    ->setAdminUserId($this->getAdminUserId())
                    ->run();
            }
        }
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
     * @param $adminUserId
     * @return $this
     */
    public function setAdminUserId($adminUserId)
    {
        $this->adminUserId = $adminUserId;
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
     * @return AdminUserRequestEmployeesSyncByAdminUserId
     */
    public function setAdminUserRequestEmployees($adminUserRequestEmployees)
    {
        $this->adminUserRequestEmployees = $adminUserRequestEmployees;
        return $this;
    }

    protected $adminRequestEmployees;

    /**
     * @return mixed
     */
    public function getAdminRequestEmployees()
    {
        return $this->adminRequestEmployees;
    }

    /**
     * @param mixed $adminRequestEmployees
     * @return AdminUserRequestEmployeesSyncByAdminUserId
     */
    public function setAdminRequestEmployees($adminRequestEmployees)
    {
        $this->adminRequestEmployees = $adminRequestEmployees;
        return $this;
    }

    protected $adminRequestIdKey = 'admin_request_id';

    /**
     * @return string
     */
    public function getAdminRequestIdKey()
    {
        return $this->adminRequestIdKey;
    }

    /**
     * @param string $adminRequestIdKey
     * @return AdminUserRequestEmployeesSyncByAdminUserId
     */
    public function setAdminRequestIdKey($adminRequestIdKey)
    {
        $this->adminRequestIdKey = $adminRequestIdKey;
        return $this;
    }

    protected $permissionAdminUserIdKey = 'permission_admin_user_id';

    /**
     * @return string
     */
    public function getPermissionAdminUserIdKey()
    {
        return $this->permissionAdminUserIdKey;
    }

    /**
     * @param string $permissionAdminUserIdKey
     * @return AdminUserRequestEmployeesSyncByAdminUserId
     */
    public function setPermissionAdminUserIdKey($permissionAdminUserIdKey)
    {
        $this->permissionAdminUserIdKey = $permissionAdminUserIdKey;
        return $this;
    }
}
