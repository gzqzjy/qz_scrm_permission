<?php

namespace Qz\Admin\Permission\Cores\AdminRole;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminRole;

class AdminRoleAdd extends Core
{
    protected function execute()
    {
        $model = AdminRole::withTrashed()
            ->firstOrCreate(Arr::whereNotNull([
                'name' => $this->getName(),
                'admin_role_group_id' => $this->getAdminRoleGroupId(),
                'customer_subsystem_id' => $this->getCustomerSubsystemId(),
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
     * @return AdminRoleAdd
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $param
     * @return AdminRoleAdd
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

    protected $name;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return AdminRoleAdd
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    protected $adminRoleGroupId;

    /**
     * @return mixed
     */
    public function getAdminRoleGroupId()
    {
        return $this->adminRoleGroupId;
    }

    /**
     * @param mixed $adminRoleGroupId
     * @return AdminRoleAdd
     */
    public function setAdminRoleGroupId($adminRoleGroupId)
    {
        $this->adminRoleGroupId = $adminRoleGroupId;
        return $this;
    }


    protected $customerSubsystemId;

    /**
     * @return mixed
     */
    public function getCustomerSubsystemId()
    {
        return $this->customerSubsystemId;
    }

    /**
     * @param mixed $customerSubsystemId
     * @return AdminRoleAdd
     */
    public function setCustomerSubsystemId($customerSubsystemId)
    {
        $this->customerSubsystemId = $customerSubsystemId;
        return $this;
    }


}
