<?php

namespace Qz\Admin\Permission\Cores\AdminUserCustomerSubsystem;

use Qz\Admin\Permission\Cores\AdminUserCustomerSubsystemMenu\AdminUserCustomerSubsystemMenuAdd;
use Qz\Admin\Permission\Cores\AdminUserCustomerSubsystemPageColumn\AdminUserCustomerSubsystemPageColumnAdd;
use Qz\Admin\Permission\Cores\AdminUserCustomerSubsystemPageOption\AdminUserCustomerSubsystemPageOptionAdd;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystemMenu;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystemPageColumn;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystemPageOption;

class AdminUserCustomerSubsystemUpdatePermission extends Core
{
    protected function execute()
    {
        if (empty($this->getId())){
            return;
        }
        AdminUserCustomerSubsystemMenu::query()
            ->where('admin_user_customer_subsystem_id', $this->getId())
            ->delete();
        AdminUserCustomerSubsystemPageColumn::query()
            ->where('admin_user_customer_subsystem_id', $this->getId())
            ->delete();
        AdminUserCustomerSubsystemPageOption::query()
            ->where('admin_user_customer_subsystem_id', $this->getId())
            ->delete();
        if ($this->getAdminMenu()){
            foreach ($this->getAdminMenu() as $adminMenu){
                AdminUserCustomerSubsystemMenuAdd::init()
                    ->setAdminUserCustomerSubsystemId($this->getId())
                    ->setAdminMenuId(Arr::get($adminMenu, 'id'))
                    ->setType(Arr::get($adminMenu, 'type'))
                    ->run();
            }
        }
        if ($this->getAdminPageColumn()){
            foreach ($this->getAdminPageColumn() as $adminPageColumn){
                AdminUserCustomerSubsystemPageColumnAdd::init()
                    ->setAdminUserCustomerSubsystemId($this->getId())
                    ->setAdminPageColumnId(Arr::get($adminPageColumn, 'id'))
                    ->setType(Arr::get($adminPageColumn, 'type'))
                    ->run();
            }
        }
        if ($this->getAdminPageOption()){
            foreach ($this->getAdminPageOption() as $adminPageOption){
                AdminUserCustomerSubsystemPageOptionAdd::init()
                    ->setAdminUserCustomerSubsystemId($this->getId())
                    ->setAdminPageOptionId(Arr::get($adminPageOption, 'id'))
                    ->setType(Arr::get($adminPageOption, 'type'))
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
     * @return AdminUserCustomerSubsystemUpdatePermission
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $param
     * @return AdminUserCustomerSubsystemUpdatePermission
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
     * @return AdminUserCustomerSubsystemUpdatePermission
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
     * @return AdminUserCustomerSubsystemUpdatePermission
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
     * @return AdminUserCustomerSubsystemUpdatePermission
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
     * @return AdminUserCustomerSubsystemUpdatePermission
     */
    public function setAdminPageOption($adminPageOption)
    {
        $this->adminPageOption = $adminPageOption;
        return $this;
    }

}
