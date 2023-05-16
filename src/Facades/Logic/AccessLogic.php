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
}
