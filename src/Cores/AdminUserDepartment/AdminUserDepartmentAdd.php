<?php
namespace Qz\Admin\Permission\Cores\AdminUserDepartment;

use Illuminate\Support\Arr;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserDepartment;

class AdminUserDepartmentAdd extends Core
{
    protected function execute()
    {
        $model = AdminUserDepartment::withTrashed()
            ->updateOrCreate(Arr::whereNotNull([
                'admin_user_id' => $this->getAdminUserId(),
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
     * @return AdminUserDepartmentAdd
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
     * @return AdminUserDepartmentAdd
     */
    public function setAdminDepartmentId($adminDepartmentId)
    {
        $this->adminDepartmentId = $adminDepartmentId;
        return $this;
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
     * @return AdminUserDepartmentAdd
     */
    public function setAdminUserId($adminUserId)
    {
        $this->adminUserId = $adminUserId;
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
     * @return AdminUserDepartmentAdd
     */
    public function setAdministrator($administrator)
    {
        $this->administrator = $administrator;
        return $this;
    }
}
