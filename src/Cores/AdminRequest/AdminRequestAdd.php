<?php
namespace Qz\Admin\Permission\Cores\AdminRequest;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminRequest;

class AdminRequestAdd extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminPageOptionId()) || empty($this->getCode())) {
            return;
        }
        $model = AdminRequest::withTrashed()
            ->updateOrCreate(Arr::whereNotNull([
                'admin_page_option_id' => $this->getAdminPageOptionId(),
                'code' => $this->getCode()
            ]),Arr::whereNotNull([
                'name' => $this->getName()
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
     * @return AdminRequestAdd
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    protected $adminPageOptionId;

    /**
     * @return mixed
     */
    public function getAdminPageOptionId()
    {
        return $this->adminPageOptionId;
    }

    /**
     * @param mixed $adminPageOptionId
     * @return AdminRequestAdd
     */
    public function setAdminPageOptionId($adminPageOptionId)
    {
        $this->adminPageOptionId = $adminPageOptionId;
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
     * @return AdminRequestAdd
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
     * @return AdminRequestAdd
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @param $param
     * @return AdminRequestAdd
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
}
