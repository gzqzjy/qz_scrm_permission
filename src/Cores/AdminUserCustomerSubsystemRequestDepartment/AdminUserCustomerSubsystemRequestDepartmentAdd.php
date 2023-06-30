<?php
namespace Qz\Admin\Permission\Cores\AdminUserCustomerSubsystemRequestDepartment;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystemRequestDepartment;

class AdminUserCustomerSubsystemRequestDepartmentAdd extends Core
{
    protected function execute()
    {
        $model = AdminUserCustomerSubsystemRequestDepartment::withTrashed()
            ->updateOrCreate(Arr::whereNotNull([
                'admin_user_customer_subsystem_id' => $this->getAdminUserCustomerSubsystemId(),
                'admin_request_id' => $this->getAdminRequestId()
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
     * @return AdminUserCustomerSubsystemRequestDepartmentAdd
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $param
     * @return AdminUserCustomerSubsystemRequestDepartmentAdd
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
     * @return AdminUserCustomerSubsystemRequestDepartmentAdd
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
     * @return AdminUserCustomerSubsystemRequestDepartmentAdd
     */
    public function setAdminUserCustomerSubsystemId($adminUserCustomerSubsystemId)
    {
        $this->adminUserCustomerSubsystemId = $adminUserCustomerSubsystemId;
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
     * @return AdminUserCustomerSubsystemRequestDepartmentAdd
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

}
