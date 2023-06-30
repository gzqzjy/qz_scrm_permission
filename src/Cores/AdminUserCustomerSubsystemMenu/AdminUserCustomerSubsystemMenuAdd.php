<?php

namespace Qz\Admin\Permission\Cores\AdminUserCustomerSubsystemMenu;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystemMenu;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AdminUserCustomerSubsystemMenuAdd extends Core
{
    protected function execute()
    {
        $model = AdminUserCustomerSubsystemMenu::withTrashed()
            ->firstOrCreate(Arr::whereNotNull([
                'admin_user_customer_subsystem_id' => $this->getAdminUserCustomerSubsystemId(),
                'admin_menu_id' => $this->getAdminMenuId(),
                'type' => $this->getType(),
            ]));
        if ($model->trashed()) {
            $model->restore();
        }
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
     * @return AdminUserCustomerSubsystemMenuAdd
     */
    protected function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $param
     * @return AdminUserCustomerSubsystemMenuAdd
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
     * @return AdminUserCustomerSubsystemMenuAdd
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
     * @return AdminUserCustomerSubsystemMenuAdd
     */
    public function setAdminMenuId($adminMenuId)
    {
        $this->adminMenuId = $adminMenuId;
        return $this;
    }

    protected $type;

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return AdminUserCustomerSubsystemMenuAdd
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

}
