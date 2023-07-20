<?php

namespace Qz\Admin\Permission\Cores\AdminUserRequestEmployee;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserRequestEmployee;

class AdminUserRequestEmployeeDelete extends Core
{
    protected function execute()
    {
        $model = AdminUserRequestEmployee::withTrashed()
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
     * @return AdminUserRequestEmployeeDelete
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
}
