<?php

namespace Qz\Admin\Permission\Cores\AdminPage;

use Qz\Admin\Permission\Cores\Core;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Qz\Admin\Permission\Models\AdminPage;

class AdminPageUpdate extends Core
{
    protected function execute()
    {
        $model = AdminPage::withTrashed()
            ->findOrFail($this->getId());
        $model->fill(Arr::whereNotNull([
            'name' => $this->getName(),
            'code' => $this->getCode(),
            'subsystem_id' => $this->getSubsystemId(),
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
     * @return AdminPageUpdate
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $param
     * @return AdminPageUpdate
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
     * @return AdminPageUpdate
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
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
     * @return AdminPageUpdate
     */
    public function setCode($code)
    {
        $this->code = $code;
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
}
