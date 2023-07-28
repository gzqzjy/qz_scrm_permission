<?php

namespace Qz\Admin\Permission\Cores\AdminUserPageColumn;

use Qz\Admin\Permission\Cores\Core;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Qz\Admin\Permission\Models\AdminUserPageColumn;

class AdminUserPageColumnAdd extends Core
{
    protected function execute()
    {
        $model = AdminUserPageColumn::withTrashed()
            ->updateOrCreate(Arr::whereNotNull([
                'admin_user_id' => $this->getAdminUserId(),
                'admin_page_column_id' => $this->getAdminPageColumnId(),
            ]), Arr::whereNotNull([
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
     * @return AdminUserPageColumnAdd
     */
    protected function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $param
     * @return AdminUserPageColumnAdd
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
     * @return AdminUserPageColumnAdd
     */
    public function setAdminUserId($adminUserId)
    {
        $this->adminUserId = $adminUserId;
        return $this;
    }

    protected $adminPageColumnId;

    /**
     * @return mixed
     */
    public function getAdminPageColumnId()
    {
        return $this->adminPageColumnId;
    }

    /**
     * @param mixed $adminPageColumnId
     * @return AdminUserPageColumnAdd
     */
    public function setAdminPageColumnId($adminPageColumnId)
    {
        $this->adminPageColumnId = $adminPageColumnId;
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
     * @return AdminUserPageColumnAdd
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
}
