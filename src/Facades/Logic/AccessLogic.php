<?php

namespace Qz\Admin\Permission\Facades\Logic;

class AccessLogic
{
    protected $subsystemId;

    /**
     * @return mixed
     */
    public function getSubsystemId()
    {
        return $this->subsystemId;
    }

    /**
     * @param mixed $subsystemId
     */
    public function setSubsystemId($subsystemId)
    {
        $this->subsystemId = $subsystemId;
    }

    protected $customerId;

    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param mixed $customerId
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
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
     * @return AccessLogic
     */
    public function setAdministrator($administrator)
    {
        $this->administrator = $administrator;
        return $this;
    }

    protected $adminUserCustomerSubsystemIds;

    /**
     * @return mixed
     */
    public function getAdminUserCustomerSubsystemIds()
    {
        return $this->adminUserCustomerSubsystemIds;
    }

    /**
     * @param mixed $adminUserCustomerSubsystemIds
     * @return AccessLogic
     */
    public function setAdminUserCustomerSubsystemIds($adminUserCustomerSubsystemIds)
    {
        $this->adminUserCustomerSubsystemIds = $adminUserCustomerSubsystemIds;
        return $this;
    }
}
