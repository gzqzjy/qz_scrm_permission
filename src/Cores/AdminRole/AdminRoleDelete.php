<?php

namespace Qz\Admin\Permission\Cores\AdminRole;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminRole;


class AdminRoleDelete extends Core
{
    protected function execute()
    {
        $model = AdminRole::withTrashed()
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
     * @return AdminRoleDelete
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
}
