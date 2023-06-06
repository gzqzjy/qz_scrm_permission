<?php

namespace Qz\Admin\Permission\Cores\AdminRoleGroup;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminRoleGroup;


class AdminRoleGroupDelete extends Core
{
    protected function execute()
    {
        $model = AdminRoleGroup::withTrashed()
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
     * @return AdminRoleGroupDelete
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
}
