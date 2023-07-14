<?php

namespace Qz\Admin\Permission\Cores\AdminDepartmentRole;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminDepartmentRole;

class AdminDepartmentRoleDelete extends Core
{
    protected function execute()
    {
        $model = AdminDepartmentRole::withTrashed()
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
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
}
