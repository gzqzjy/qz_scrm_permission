<?php
namespace Qz\Admin\Permission\Cores\AdminDepartmentRole;

use Illuminate\Support\Arr;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminDepartmentRole;

class AdminDepartmentRoleAdd extends Core
{
    protected function execute()
    {
        $model = AdminDepartmentRole::withTrashed()
            ->firstOrCreate(Arr::whereNotNull([
                'admin_role_id' => $this->getAdminRoleId(),
                'admin_department_id' => $this->getAdminDepartmentId(),
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
     * @return AdminDepartmentRoleAdd
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    
    
    
    protected $adminRoleId;

    /**
     * @return mixed
     */
    public function getAdminRoleId()
    {
        return $this->adminRoleId;
    }

    /**
     * @param mixed $adminRoleId
     * @return AdminDepartmentRoleAdd
     */
    public function setAdminRoleId($adminRoleId)
    {
        $this->adminRoleId = $adminRoleId;
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
     * @return AdminDepartmentRoleAdd
     */
    public function setAdminDepartmentId($adminDepartmentId)
    {
        $this->adminDepartmentId = $adminDepartmentId;
        return $this;
    }


}
