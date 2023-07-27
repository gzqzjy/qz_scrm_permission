<?php

namespace Qz\Admin\Permission\Cores\AdminRolePageColumn;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminRolePageColumn;

class AdminRolePageColumnDelete extends Core
{
    protected function execute()
    {
        $model = AdminRolePageColumn::withTrashed()
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
     * @return AdminRolePageColumnDelete
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
}
