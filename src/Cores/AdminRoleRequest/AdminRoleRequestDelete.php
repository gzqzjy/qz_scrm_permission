<?php

namespace Qz\Admin\Permission\Cores\AdminRoleRequest;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminRoleRequest;

class AdminRoleRequestDelete extends Core
{
    protected function execute()
    {
        $model = AdminRoleRequest::withTrashed()
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
     * @return AdminRoleRequestDelete
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
}
