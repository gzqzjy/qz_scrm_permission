<?php

namespace Qz\Admin\Permission\Facades\Logic;

class RequestIdLogic
{
    protected $requestId;

    /**
     * @return mixed
     */
    public function get()
    {
        return $this->requestId;
    }

    /**
     * @param mixed $requestId
     * @return RequestIdLogic
     */
    public function set($requestId)
    {
        $this->requestId = $requestId;
        return $this;
    }
}
