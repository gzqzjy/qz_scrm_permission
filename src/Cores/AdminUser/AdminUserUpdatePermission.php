<?php

namespace Qz\Admin\Permission\Cores\AdminUser;

use Qz\Admin\Permission\Cores\AdminUserMenu\AdminUserMenuAdd;
use Qz\Admin\Permission\Cores\AdminUserPageColumn\AdminUserPageColumnAdd;
use Qz\Admin\Permission\Cores\AdminUserPageOption\AdminUserPageOptionAdd;
use Qz\Admin\Permission\Cores\AdminUserRequestDepartment\AdminUserRequestDepartmentAdd;
use Qz\Admin\Permission\Cores\AdminUserRequestEmployee\AdminUserRequestEmployeeAdd;
use Qz\Admin\Permission\Cores\Core;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Qz\Admin\Permission\Models\AdminUserMenu;
use Qz\Admin\Permission\Models\AdminUserPageColumn;
use Qz\Admin\Permission\Models\AdminUserPageOption;
use Qz\Admin\Permission\Models\AdminUserRequestDepartment;
use Qz\Admin\Permission\Models\AdminUserRequestEmployee;

class AdminUserUpdatePermission extends Core
{
    protected function execute()
    {
        if (empty($this->getId())){
            return;
        }
        AdminUserMenu::query()
            ->where('admin_user_id', $this->getId())
            ->delete();
        AdminUserPageColumn::query()
            ->where('admin_user_id', $this->getId())
            ->delete();
        AdminUserPageOption::query()
            ->where('admin_user_id', $this->getId())
            ->delete();

        AdminUserRequestDepartment::query()
            ->where('admin_user_id', $this->getId())
            ->delete();

        AdminUserRequestEmployee::query()
            ->where('admin_user_id', $this->getId())
            ->delete();

        if ($this->getAdminMenu()){
            foreach ($this->getAdminMenu() as $adminMenu){
                AdminUserMenuAdd::init()
                    ->setAdminUserId($this->getId())
                    ->setAdminMenuId(Arr::get($adminMenu, 'id'))
                    ->setType(Arr::get($adminMenu, 'type'))
                    ->run();
            }
        }
        if ($this->getAdminPageColumn()){
            foreach ($this->getAdminPageColumn() as $adminPageColumn){
                AdminUserPageColumnAdd::init()
                    ->setAdminUserId($this->getId())
                    ->setAdminPageColumnId(Arr::get($adminPageColumn, 'id'))
                    ->setType(Arr::get($adminPageColumn, 'type'))
                    ->run();
            }
        }
        if ($this->getAdminPageOption()){
            foreach ($this->getAdminPageOption() as $adminPageOption){
                AdminUserPageOptionAdd::init()
                    ->setAdminUserId($this->getId())
                    ->setAdminPageOptionId(Arr::get($adminPageOption, 'id'))
                    ->setType(Arr::get($adminPageOption, 'type'))
                    ->run();
            }
        }

        if ($this->getAdminUserRequestDepartments()){
            foreach ($this->getAdminUserRequestDepartments() as $adminUserRequestDepartment){
                AdminUserRequestDepartmentAdd::init()
                    ->setAdminUserId($this->getId())
                    ->setAdminRequestId(Arr::get($adminUserRequestDepartment, 'admin_request_id'))
                    ->setType(Arr::get($adminUserRequestDepartment, 'type'))
                    ->run();
            }
        }

        if ($this->getAdminUserRequestEmployees()){
            foreach ($this->getAdminUserRequestEmployees() as $adminUserRequestEmployee){
                AdminUserRequestEmployeeAdd::init()
                    ->setAdminUserId($this->getId())
                    ->setAdminRequestId(Arr::get($adminUserRequestEmployee, 'admin_request_id'))
                    ->setPermissionAdminUserId(Arr::get($adminUserRequestEmployee, 'admin_user_id'))
                    ->setType(Arr::get($adminUserRequestEmployee, 'type'))
                    ->run();
            }
        }
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
    public function getAdminUserRequestDepartments()
    {
        return $this->adminUserRequestDepartments;
    }

    /**
     * @param mixed $adminUserRequestDepartments
     * @return AdminUserUpdatePermission
     */
    public function setAdminUserRequestDepartments($adminUserRequestDepartments)
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
