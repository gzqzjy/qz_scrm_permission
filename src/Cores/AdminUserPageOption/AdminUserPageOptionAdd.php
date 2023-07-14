<?php

namespace Qz\Admin\Permission\Cores\AdminUserPageOption;

use Qz\Admin\Permission\Cores\Core;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Qz\Admin\Permission\Models\AdminUserPageOption;

class AdminUserPageOptionAdd extends Core
{
    protected function execute()
    {
        $model = AdminUserPageOption::withTrashed()
            ->firstOrCreate(Arr::whereNotNull([
                'admin_user_id' => $this->getAdminUserId(),
                'admin_page_option_id' => $this->getAdminPageOptionId(),
                'type' => $this->getType(),
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
     * @return AdminUserPageOptionAdd
     */
    protected function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $param
     * @return AdminUserPageOptionAdd
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

    protected $adminUserId;

    /**
     * @return mixed
     */
    public function getAdminUserId()
    {
        return $this->adminUserId;
    }

    /**
     * @param mixed $adminUserId
     * @return AdminUserPageOptionAdd
     */
    public function setAdminUserId($adminUserId)
    {
        $this->adminUserId = $adminUserId;
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
     * @return AdminUserPageOptionAdd
     */
    public function setAdminPageOptionId($adminPageOptionId)
    {
        $this->adminPageOptionId = $adminPageOptionId;
        return $this;
    }


    protected $type;

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return AdminUserPageOptionAdd
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

}
