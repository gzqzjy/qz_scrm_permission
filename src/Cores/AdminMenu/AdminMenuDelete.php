<?php

namespace Qz\Admin\Permission\Cores\AdminMenu;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminMenu;

class AdminMenuDelete extends Core
{
    protected function execute()
    {
        $model = AdminMenu::withTrashed()
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
     * @return AdminMenuDelete
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
}
