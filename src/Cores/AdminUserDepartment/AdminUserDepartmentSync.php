<?php

namespace Qz\Admin\Permission\Cores\AdminUserDepartment;

use Illuminate\Support\Arr;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserDepartment;

class AdminUserDepartmentSync extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminUserId())) {
            return;
        }
        if (is_null($this->getAdminUserDepartments())) {
            return;
        }
        AdminUserDepartment::query()
            ->where('admin_user_id', $this->getAdminUserId())
            ->whereNotIn('admin_department_id', $this->getAdminUserDepartments())
            ->delete();
        $adminUserDepartments = $this->getAdminUserDepartments();
        if (!empty($adminUserDepartments)) {
            foreach ($adminUserDepartments as $adminUserDepartment) {
                AdminUserDepartmentAdd::init()
                    ->setAdminUserId($this->getAdminUserId())
                    ->setAdminDepartmentId(Arr::get($adminUserDepartment, 'admin_department_id'))
                    ->setAdministrator((boolean) Arr::get($adminUserDepartment, 'administrator'))
                    ->run();
            }
        }
    }

    protected $adminUserId;

    /**
     * @return mixed
     */
    public function getAdminUserId()
    {
        return $this->adminUserId;
    }

    /**
     * @param mixed $adminUserId
     * @return AdminUserDepartmentSync
     */
    public function setAdminUserId($adminUserId)
    {
        $this->adminUserId = $adminUserId;
        return $this;
    }

    protected $adminUserDepartments;

    /**
     * @return mixed
     */
    public function getAdminUserDepartments()
    {
        return $this->adminUserDepartments;
    }

    /**
     * @param mixed $adminUserDepartments
     * @return AdminUserDepartmentSync
     */
    public function setAdminUserDepartments($adminUserDepartments)
    {
        $this->adminUserDepartments = $adminUserDepartments;
        return $this;
    }
}
