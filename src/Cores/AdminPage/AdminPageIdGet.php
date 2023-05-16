<?php

namespace Qz\Admin\Permission\Cores\AdminPage;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminPage;

class AdminPageIdGet extends Core
{
    protected function execute()
    {
        if (!empty($code) && !empty($this->getSubsystemId())) {
            $model = AdminPage::query()
                ->where('code', $code)
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
     * @param $code
     * @return $this
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
     */
    public function setId($id)
    {
        $this->id = $id;
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
}
