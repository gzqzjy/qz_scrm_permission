<?php

namespace Qz\Admin\Permission\Cores\AdminUser;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUser;

class AdminUserDelete extends Core
{
    protected function execute()
    {
        $model = AdminUser::withTrashed()
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
     * @return AdminUserDelete
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
}
