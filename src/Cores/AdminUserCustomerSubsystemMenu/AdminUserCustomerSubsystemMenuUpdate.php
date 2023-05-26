<?php

namespace Qz\Admin\Permission\Cores\AdminUserCustomerSubsystemMenu;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystemMenu;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AdminUserCustomerSubsystemMenuUpdate extends Core
{
    protected function execute()
    {
        $model = AdminUserCustomerSubsystemMenu::withTrashed()
            ->findOrFail($this->getId());
        $model->fill(Arr::whereNotNull([
            'admin_user_customer_subsystem_id' => $this->getAdminUserCustomerSubsystemId(),
            'admin_menu_id' => $this->getAdminMenuId(),
        ]));
        $model->save();
        $this->setId($model->getKey());
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
     * @return AdminUserCustomerSubsystemMenuUpdate
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $param
     * @return AdminUserCustomerSubsystemMenuUpdate
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
     * @return AdminUserCustomerSubsystemMenuUpdate
     */
    public function setAdminUserCustomerSubsystemId($adminUserCustomerSubsystemId)
    {
        $this->adminUserCustomerSubsystemId = $adminUserCustomerSubsystemId;
        return $this;
    }
    protected $adminMenuId;

    /**
     * @return mixed
     */
    public function getAdminMenuId()
    {
        return $this->adminMenuId;
    }

    /**
     * @param mixed $adminMenuId
     * @return AdminUserCustomerSubsystemMenuUpdate
     */
    public function setAdminMenuId($adminMenuId)
    {
        $this->adminMenuId = $adminMenuId;
        return $this;
    }
}
