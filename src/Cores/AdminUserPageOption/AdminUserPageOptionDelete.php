<?php

namespace Qz\Admin\Permission\Cores\AdminUserPageOption;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserPageOption;

class AdminUserPageOptionDelete extends Core
{
    protected function execute()
    {
        $model = AdminUserPageOption::withTrashed()
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
     * @return AdminUserPageOptionDelete
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
}
