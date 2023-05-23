<?php

namespace Qz\Admin\Permission\Cores\AdminUserCustomerSubsystem;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystem;

class AdminUserCustomerSubsystemDelete extends Core
{
    protected function execute()
    {
        $model = AdminUserCustomerSubsystem::withTrashed()
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
     * @return AdminUserCustomerSubsystemDelete
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
}
