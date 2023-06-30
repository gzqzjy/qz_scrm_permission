<?php


namespace Qz\Admin\Permission\Cores\AdminRequest;


use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminRequest;

class AdminRequestIdGet extends Core
{
    protected function execute()
    {
        if (!empty($this->getCode()) && !empty($this->getSubsystemId())) {
            $model = AdminRequest::query()
                ->where('code', $this->getCode())
                ->where('subsystem_id', $this->getSubsystemId())
                ->first();
            if (!empty($model)) {
                $this->setId($model->getKey());
            }
        }
    }

    protected $code;

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     * @return AdminRequestIdGet
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
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
     * @return AdminRequestIdGet
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @return AdminRequestIdGet
     */
    public function setSubsystemId($subsystemId)
    {
        $this->subsystemId = $subsystemId;
        return $this;
    }

}
