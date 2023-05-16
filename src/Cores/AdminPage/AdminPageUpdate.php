<?php

namespace Qz\Admin\Access\Cores\AdminPage;

use Qz\Admin\Access\Cores\Core;
use App\Models\AdminPage;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AdminPageUpdate extends Core
{
    protected function execute()
    {
        $model = AdminPage::withTrashed()
            ->findOrFail($this->getId());
        $model->fill(Arr::whereNotNull([
            'name' => $this->getName(),
            'code' => $this->getCode(),
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
}
