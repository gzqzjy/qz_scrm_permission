<?php
namespace Qz\Admin\Permission\Cores\AdminUserCustomerSubsystemDepartment;

use Illuminate\Support\Arr;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystemDepartment;

class AdminUserCustomerSubsystemDepartmentAdd extends Core
{
    protected function execute()
    {
        $model = AdminUserCustomerSubsystemDepartment::withTrashed()
            ->updateOrCreate(Arr::whereNotNull([
                'admin_user_customer_subsystem_id' => $this->getAdminUserCustomerSubsystemId(),
                'admin_department_id' => $this->getAdminDepartmentId(),
            ]), Arr::whereNotNull([
                'administrator' => $this->getAdministrator(),
            ]));
        if ($model->trashed()) {
            $model->restore();
        }
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
     * @return AdminUserCustomerSubsystemDepartmentAdd
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    protected $adminDepartmentId;

    /**
     * @return mixed
     */
    public function getAdminDepartmentId()
    {
        return $this->adminDepartmentId;
    }

    /**
     * @param mixed $adminDepartmentId
     * @return AdminUserCustomerSubsystemDepartmentAdd
     */
    public function setAdminDepartmentId($adminDepartmentId)
    {
        $this->adminDepartmentId = $adminDepartmentId;
        return $this;
    }

    protected $adminUserCustomerSubsystemId;

    /**
     * @return mixed
     */
    public function getAdminUserCustomerSubsystemId()
    {
        return $this->adminUserCustomerSubsystemId;
    }

    /**
     * @param mixed $adminUserCustomerSubsystemId
     * @return AdminUserCustomerSubsystemDepartmentAdd
     */
    public function setAdminUserCustomerSubsystemId($adminUserCustomerSubsystemId)
    {
        $this->adminUserCustomerSubsystemId = $adminUserCustomerSubsystemId;
        return $this;
    }

    protected $administrator;

    /**
     * @return mixed
     */
    public function getAdministrator()
    {
        return $this->administrator;
    }

    /**
     * @param mixed $administrator
     * @return AdminUserCustomerSubsystemDepartmentAdd
     */
    public function setAdministrator($administrator)
    {
        $this->administrator = $administrator;
        return $this;
    }
}
