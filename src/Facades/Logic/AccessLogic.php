<?php

namespace Qz\Admin\Permission\Facades\Logic;

class AccessLogic
{
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
     * @return AccessLogic
     */
    public function setCustomerSubsystemId($customerSubsystemId)
    {
        $this->customerSubsystemId = $customerSubsystemId;
        return $this;
    }

    
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
     * @return AccessLogic
     */
    public function setAdminUserCustomerSubsystemId($adminUserCustomerSubsystemId)
    {
        $this->adminUserCustomerSubsystemId = $adminUserCustomerSubsystemId;
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
     * @return AccessLogic
     */
    public function setAdminUserId($adminUserId)
    {
        $this->adminUserId = $adminUserId;
        return $this;
    }
    
    
}
