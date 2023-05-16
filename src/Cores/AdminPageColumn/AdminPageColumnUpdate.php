<?php

namespace Qz\Admin\Access\Cores\AdminPageColumn;

use Qz\Admin\Access\Cores\Core;
use App\Models\AdminPageColumn;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AdminPageColumnUpdate extends Core
{
    protected function execute()
    {
        $model = AdminPageColumn::withTrashed()
            ->findOrFail($this->getId());
        $model->fill(Arr::whereNotNull([
            'admin_page_id' => $this->getAdminPageId(),
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
     * @return AdminPageColumnUpdate
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $param
     * @return AdminPageColumnUpdate
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


    protected $adminPageId;

    /**
     * @return mixed
     */
    public function getAdminPageId()
    {
        return $this->adminPageId;
    }

    /**
     * @param mixed $adminPageId
     * @return AdminPageColumnUpdate
     */
    public function setAdminPageId($adminPageId)
    {
        $this->adminPageId = $adminPageId;
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
     * @return AdminPageColumnUpdate
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
     * @return AdminPageColumnUpdate
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }
}
