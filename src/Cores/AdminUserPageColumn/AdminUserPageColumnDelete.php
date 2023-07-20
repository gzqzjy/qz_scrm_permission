<?php

namespace Qz\Admin\Permission\Cores\AdminUserPageColumn;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserPageColumn;

class AdminUserPageColumnDelete extends Core
{
    protected function execute()
    {
        $model = AdminUserPageColumn::withTrashed()
            ->findOrFail($this->getId());
        $model->delete();
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
     * @return AdminUserPageColumnDelete
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
}
