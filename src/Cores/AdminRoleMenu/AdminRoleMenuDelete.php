<?php

namespace Qz\Admin\Permission\Cores\AdminRoleMenu;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminRoleMenu;

class AdminRoleMenuDelete extends Core
{
    protected function execute()
    {
        $model = AdminRoleMenu::withTrashed()
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
     * @return AdminRoleMenuDelete
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
}
