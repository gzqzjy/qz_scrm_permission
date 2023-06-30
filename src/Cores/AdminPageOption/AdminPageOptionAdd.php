<?php

namespace Qz\Admin\Permission\Cores\AdminPageOption;

use Qz\Admin\Permission\Cores\Core;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Qz\Admin\Permission\Models\AdminPageOption;

class AdminPageOptionAdd extends Core
{
    protected function execute()
    {
        $model = AdminPageOption::withTrashed()
            ->updateOrCreate(Arr::whereNotNull([
                'admin_page_id' => $this->getAdminPageId(),
                'code' => $this->getCode(),
            ]), Arr::whereNotNull([
                'name' => $this->getName(),
                'is_show' => $this->getIsShow(),
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
     * @return AdminPageOptionAdd
     */
    protected function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $param
     * @return AdminPageOptionAdd
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
     * @return AdminPageOptionAdd
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
     * @return AdminPageOptionAdd
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
     * @return AdminPageOptionAdd
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    protected $isShow;

    /**
     * @return mixed
     */
    public function getIsShow()
    {
        return $this->isShow;
    }

    /**
     * @param mixed $isShow
     * @return AdminPageOptionAdd
     */
    public function setIsShow($isShow)
    {
        $this->isShow = $isShow;
        return $this;
    }

}
