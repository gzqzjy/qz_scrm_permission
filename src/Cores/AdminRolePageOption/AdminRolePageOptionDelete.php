<?php

namespace Qz\Admin\Permission\Cores\AdminRolePageOption;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminRolePageOption;

class AdminRolePageOptionDelete extends Core
{
    protected function execute()
    {
        $model = AdminRolePageOption::withTrashed()
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
     * @return AdminRolePageOptionDelete
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
}
