<?php

namespace Qz\Admin\Permission\Cores\AdminUserCustomerSubsystemPageColumn;

use Qz\Admin\Permission\Cores\Core;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystemPageColumn;

class AdminUserCustomerSubsystemPageColumnAdd extends Core
{
    protected function execute()
    {
        $model = AdminUserCustomerSubsystemPageColumn::withTrashed()
            ->firstOrCreate(Arr::whereNotNull([
                'admin_user_customer_subsystem_id' => $this->getAdminUserCustomerSubsystemId(),
                'admin_page_column_id' => $this->getAdminPageColumnId(),
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
     * @return AdminUserCustomerSubsystemPageColumnAdd
     */
    protected function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $param
     * @return AdminUserCustomerSubsystemPageColumnAdd
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
     * @return AdminUserCustomerSubsystemPageColumnAdd
     */
    public function setAdminUserCustomerSubsystemId($adminUserCustomerSubsystemId)
    {
        $this->adminUserCustomerSubsystemId = $adminUserCustomerSubsystemId;
        return $this;
    }

    protected $adminPageColumnId;

    /**
     * @return mixed
     */
    public function getAdminPageColumnId()
    {
        return $this->adminPageColumnId;
    }

    /**
     * @param mixed $adminPageColumnId
     * @return AdminUserCustomerSubsystemPageColumnAdd
     */
    public function setAdminPageColumnId($adminPageColumnId)
    {
        $this->adminPageColumnId = $adminPageColumnId;
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
     * @return AdminUserCustomerSubsystemPageColumnAdd
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

}
