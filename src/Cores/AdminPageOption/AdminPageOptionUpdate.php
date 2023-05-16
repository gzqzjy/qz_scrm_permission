<?php

namespace Qz\Admin\Access\Cores\AdminPageOption;

use Qz\Admin\Access\Cores\Core;
use App\Models\AdminPageOption;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AdminPageOptionUpdate extends Core
{
    protected function execute()
    {
        $model = AdminPageOption::withTrashed()
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
     * @return AdminPageOptionUpdate
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $param
     * @return AdminPageOptionUpdate
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
     * @return AdminPageOptionUpdate
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
     * @return AdminPageOptionUpdate
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
     * @return AdminPageOptionUpdate
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }
}
