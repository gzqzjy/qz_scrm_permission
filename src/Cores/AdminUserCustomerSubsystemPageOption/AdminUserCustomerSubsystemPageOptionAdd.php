<?php

namespace Qz\Admin\Permission\Cores\AdminUserCustomerSubsystemPageOption;

use Qz\Admin\Permission\Cores\Core;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystemPageOption;

class AdminUserCustomerSubsystemPageOptionAdd extends Core
{
    protected function execute()
    {
        $model = AdminUserCustomerSubsystemPageOption::withTrashed()
            ->firstOrCreate(Arr::whereNotNull([
                'admin_user_customer_subsystem_id' => $this->getAdminUserCustomerSubsystemId(),
                'admin_page_option_id' => $this->getAdminPageOptionId(),
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
     * @return AdminUserCustomerSubsystemPageOptionAdd
     */
    protected function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $param
     * @return AdminUserCustomerSubsystemPageOptionAdd
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
     * @return AdminUserCustomerSubsystemPageOptionAdd
     */
    public function setAdminUserCustomerSubsystemId($adminUserCustomerSubsystemId)
    {
        $this->adminUserCustomerSubsystemId = $adminUserCustomerSubsystemId;
        return $this;
    }

    protected $adminPageOptionId;

    /**
     * @return mixed
     */
    public function getAdminPageOptionId()
    {
        return $this->adminPageOptionId;
    }

    /**
     * @param mixed $adminPageOptionId
     * @return AdminUserCustomerSubsystemPageOptionAdd
     */
    public function setAdminPageOptionId($adminPageOptionId)
    {
        $this->adminPageOptionId = $adminPageOptionId;
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
     * @return AdminUserCustomerSubsystemPageOptionAdd
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

}
