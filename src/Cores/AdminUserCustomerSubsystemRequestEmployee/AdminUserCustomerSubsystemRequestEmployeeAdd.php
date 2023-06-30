<?php
namespace Qz\Admin\Permission\Cores\AdminUserCustomerSubsystemRequestEmployee;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystemRequestEmployee;

class AdminUserCustomerSubsystemRequestEmployeeAdd extends Core
{
    protected function execute()
    {
        $model = AdminUserCustomerSubsystemRequestEmployee::withTrashed()
            ->updateOrCreate(Arr::whereNotNull([
                'admin_user_customer_subsystem_id' => $this->getAdminUserCustomerSubsystemId(),
                'admin_request_id' => $this->getAdminRequestId(),
                'permission_admin_user_customer_subsystem_id' => $this->getPermissionAdminUserCustomerSubsystemId(),
            ]), Arr::whereNotNull([
                'type' => $this->getType()
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
     * @return AdminUserCustomerSubsystemRequestEmployeeAdd
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $param
     * @return AdminUserCustomerSubsystemRequestEmployeeAdd
     */
    public function setParam($param)
    {
        foreach ($param as $key => $value) {
            $setMethod = 'set' . Str::studly($key);
            if (method_exists($this, $setMethod)) {
                call_user_func([$this, $setMethod], $value);
            }
        }
        return $this;
    }

    protected $adminRequestId;

    /**
     * @return mixed
     */
    public function getAdminRequestId()
    {
        return $this->adminRequestId;
    }

    /**
     * @param mixed $adminRequestId
     * @return AdminUserCustomerSubsystemRequestEmployeeAdd
     */
    public function setAdminRequestId($adminRequestId)
    {
        $this->adminRequestId = $adminRequestId;
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
     * @return AdminUserCustomerSubsystemRequestEmployeeAdd
     */
    public function setAdminUserCustomerSubsystemId($adminUserCustomerSubsystemId)
    {
        $this->adminUserCustomerSubsystemId = $adminUserCustomerSubsystemId;
        return $this;
    }

    protected $permissionAdminUserCustomerSubsystemId;

    /**
     * @return mixed
     */
    public function getPermissionAdminUserCustomerSubsystemId()
    {
        return $this->permissionAdminUserCustomerSubsystemId;
    }

    /**
     * @param mixed $permissionAdminUserCustomerSubsystemId
     * @return AdminUserCustomerSubsystemRequestEmployeeAdd
     */
    public function setPermissionAdminUserCustomerSubsystemId($permissionAdminUserCustomerSubsystemId)
    {
        $this->permissionAdminUserCustomerSubsystemId = $permissionAdminUserCustomerSubsystemId;
        return $this;
    }



    protected $type;

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return AdminUserCustomerSubsystemRequestEmployeeAdd
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

}
