<?php

namespace Qz\Admin\Permission\Cores\AdminRole;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminDepartmentRole;
use Qz\Admin\Permission\Models\AdminRoleMenu;
use Qz\Admin\Permission\Models\AdminRolePageColumn;
use Qz\Admin\Permission\Models\AdminRolePageOption;
use Qz\Admin\Permission\Models\AdminRole;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystemRole;


class AdminRoleDelete extends Core
{
    protected function execute()
    {
        $model = AdminRole::withTrashed()
            ->findOrFail($this->getId());
        $model->delete();
        $this->setId($model->getKey());
        AdminDepartmentRole::query()
            ->where('admin_role_id', $this->getId())
            ->delete();
        AdminUserCustomerSubsystemRole::query()
            ->where('admin_role_id', $this->getId())
            ->delete();
        AdminRoleMenu::query()
            ->where('admin_role_id', $this->getId())
            ->delete();
        AdminRolePageColumn::query()
            ->where('admin_role_id', $this->getId())
            ->delete();
        AdminRolePageOption::query()
            ->where('admin_role_id', $this->getId())
            ->delete();
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
