<?php

namespace Qz\Admin\Permission\Cores\AdminUserCustomerSubsystemMenu;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystemMenu;

class AdminUserCustomerSubsystemMenuDelete extends Core
{
    protected function execute()
    {
        $model = AdminUserCustomerSubsystemMenu::withTrashed()
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
     * @return AdminUserCustomerSubsystemMenuDelete
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
}
