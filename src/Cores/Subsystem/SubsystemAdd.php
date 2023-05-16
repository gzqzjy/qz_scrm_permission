<?php

namespace Qz\Admin\Access\Cores\Subsystem;

use Qz\Admin\Access\Cores\Core;
use App\Models\Subsystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SubsystemAdd extends Core
{
    protected function execute()
    {
        $model = Subsystem::withTrashed()
            ->firstOrCreate(Arr::whereNotNull([
                'app_key' => $this->getAppKey(),
            ]), Arr::whereNotNull([
                'name' => $this->getName(),
            ]));
        if ($model->trashed()) {
            $model->restore();
        }
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
     * @return SubsystemAdd
     */
    protected function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $param
     * @return SubsystemAdd
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
     * @return SubsystemAdd
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    protected $appKey;

    /**
     * @return mixed
     */
    public function getAppKey()
    {
        return $this->appKey;
    }

    /**
     * @param mixed $appKey
     * @return SubsystemAdd
     */
    public function setAppKey($appKey)
    {
        $this->appKey = $appKey;
        return $this;
    }
}
