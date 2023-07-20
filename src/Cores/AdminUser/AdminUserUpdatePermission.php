<?php

namespace Qz\Admin\Permission\Cores\AdminUser;

use Qz\Admin\Permission\Cores\AdminUserMenu\AdminUserMenusSyncByAdminUserId;
use Qz\Admin\Permission\Cores\AdminUserPageColumn\AdminUserPageColumnsSyncByAdminUserId;
use Qz\Admin\Permission\Cores\AdminUserPageOption\AdminUserPageOptionsSyncByAdminUserId;
use Qz\Admin\Permission\Cores\AdminUserRequest\AdminUserRequestsSyncByAdminUserId;
use Qz\Admin\Permission\Cores\AdminUserRequestEmployee\AdminUserRequestEmployeesSyncByAdminUserId;
use Qz\Admin\Permission\Cores\Core;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AdminUserUpdatePermission extends Core
{
    protected function execute()
    {
        if (empty($this->getId())) {
            return;
        }
        AdminUserMenusSyncByAdminUserId::init()
            ->setAdminUserId($this->getId())
            ->setAdminUserMenus($this->getAdminMenu())
            ->setAdminMenuIdKey('id')
            ->run();
        AdminUserPageColumnsSyncByAdminUserId::init()
            ->setAdminUserId($this->getId())
            ->setAdminUserPageColumns($this->getAdminPageColumn())
            ->setAdminPageColumnIdKey('id')
            ->run();
        AdminUserPageOptionsSyncByAdminUserId::init()
            ->setAdminUserId($this->getId())
            ->setAdminUserPageOptions($this->getAdminPageOption())
            ->setAdminPageOptionIdKey('id')
            ->run();

        AdminUserRequestsSyncByAdminUserId::init()
            ->setAdminUserId($this->getId())
            ->setAdminUserRequests($this->getAdminUserRequests())
            ->setAdminRequestIdKey('id')
            ->run();

        AdminUserRequestEmployeesSyncByAdminUserId::init()
            ->setAdminUserId($this->getId())
            ->setAdminRequestEmployees($this->getAdminUserRequestEmployees())
            ->setAdminRequestIdKey('admin_request_id')
            ->setPermissionAdminUserIdKey('admin_user_id')
            ->run();
    }

    protected $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return AdminUserUpdatePermission
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $param
     * @return AdminUserUpdatePermission
     */
    public function setParam($param)
    {
        foreach ($param as $key => $value) {
            $setMethod = 'set' . Str::studly($key);
            if (method_exists($this, $setMethod)) {
                call_user_func([$this, $setMethod], $value);
            }
        }
        return $this;
    }

    protected $permission;

    protected $adminMenu;

    protected $adminPageColumn;

    protected $adminPageOption;

    /**
     * @return mixed
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * @param mixed $permission
     * @return AdminUserUpdatePermission
     */
    public function setPermission($permission)
    {
        $this->permission = $permission;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAdminMenu()
    {
        return $this->adminMenu;
    }

    /**
     * @param mixed $adminMenu
     * @return AdminUserUpdatePermission
     */
    public function setAdminMenu($adminMenu)
    {
        $this->adminMenu = $adminMenu;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAdminPageColumn()
    {
        return $this->adminPageColumn;
    }

    /**
     * @param mixed $adminPageColumn
     * @return AdminUserUpdatePermission
     */
    public function setAdminPageColumn($adminPageColumn)
    {
        $this->adminPageColumn = $adminPageColumn;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAdminPageOption()
    {
        return $this->adminPageOption;
    }

    /**
     * @param mixed $adminPageOption
     * @return AdminUserUpdatePermission
     */
    public function setAdminPageOption($adminPageOption)
    {
        $this->adminPageOption = $adminPageOption;
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
     * @return AdminUserUpdatePermission
     */
    public function setAdminUserRequests($adminUserRequestDepartments)
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
     * @return AdminUserUpdatePermission
     */
    public function setAdminUserRequestEmployees($adminUserRequestEmployees)
    {
        $this->adminUserRequestEmployees = $adminUserRequestEmployees;
        return $this;
    }
}
