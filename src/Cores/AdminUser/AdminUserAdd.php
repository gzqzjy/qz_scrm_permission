<?php

namespace Qz\Admin\Permission\Cores\AdminUser;

use Qz\Admin\Permission\Cores\AdminUserCustomerSubsystem\AdminUserCustomerSubsystemAdd;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUser;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystem;

class AdminUserAdd extends Core
{
    protected function execute()
    {
        $model = AdminUser::withTrashed()
            ->firstOrCreate(Arr::whereNotNull([
                'mobile' => $this->getMobile(),
            ]), Arr::whereNotNull([
                'name' => $this->getName(),
                'status' => $this->getStatus(),
            ]));
        if ($model->trashed()) {
            $model->restore();
        }
        $this->setId($model->getKey());
        if ($this->getCustomerSubsystemId()) {
            AdminUserCustomerSubsystemAdd::init()
                ->setCustomerSubsystemId($this->getCustomerSubsystemId())
                ->setStatus(AdminUserCustomerSubsystem::STATUS_NORMAL)
                ->setAdminUserId($this->getId())
                ->setAdministrator(false)
                ->run();
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
     * @return AdminUserAdd
     */
    protected function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $param
     * @return AdminUserAdd
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
     * @return AdminUserAdd
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    protected $mobile;

    /**
     * @return mixed
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param mixed $mobile
     * @return AdminUserAdd
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
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
     * @return AdminUserAdd
     */
    public function setStatus($status)
    {
        $this->status = $status;
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
     * @return AdminUserAdd
     */
    public function setCustomerSubsystemId($customerSubsystemId)
    {
        $this->customerSubsystemId = $customerSubsystemId;
        return $this;
    }
}
