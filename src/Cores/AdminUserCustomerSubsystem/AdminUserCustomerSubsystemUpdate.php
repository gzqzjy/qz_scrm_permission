<?php

namespace Qz\Admin\Permission\Cores\AdminUserCustomerSubsystem;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AdminUserCustomerSubsystemUpdate extends Core
{
    protected function execute()
    {
        $model = AdminUserCustomerSubsystem::withTrashed()
            ->findOrFail($this->getId());
        $model->fill(Arr::whereNotNull([
            'admin_user_id' => $this->getAdminUserId(),
            'customer_subsystem_id' => $this->getCustomerSubsystemId(),
            'status' => $this->getStatus(),
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
     * @return AdminUserCustomerSubsystemUpdate
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $param
     * @return AdminUserCustomerSubsystemUpdate
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

    protected $adminUserId;

    /**
     * @return mixed
     */
    public function getAdminUserId()
    {
        return $this->adminUserId;
    }

    /**
     * @param mixed $adminUserId
     * @return AdminUserCustomerSubsystemUpdate
     */
    public function setAdminUserId($adminUserId)
    {
        $this->adminUserId = $adminUserId;
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
     * @return AdminUserCustomerSubsystemUpdate
     */
    public function setCustomerSubsystemId($customerSubsystemId)
    {
        $this->customerSubsystemId = $customerSubsystemId;
        return $this;
    }

    protected $status;

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     * @return AdminUserCustomerSubsystemUpdate
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    protected $administrator;

    /**
     * @return mixed
     */
    public function getAdministrator()
    {
        return $this->administrator;
    }

    /**
     * @param mixed $administrator
     * @return AdminUserCustomerSubsystemUpdate
     */
    public function setAdministrator($administrator)
    {
        $this->administrator = $administrator;
        return $this;
    }
}
