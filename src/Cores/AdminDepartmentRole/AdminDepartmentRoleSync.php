<?php
namespace Qz\Admin\Permission\Cores\AdminDepartmentRole;

use Illuminate\Support\Arr;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminDepartmentRole;

class AdminDepartmentRoleSync extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminDepartmentId())) {
            return;
        }
        if (is_null($this->getAdminRoleIds())) {
            return;
        }
        $adminDepartmentRoles = AdminDepartmentRole::query()
            ->select(['id'])
            ->where('admin_department_id', $this->getAdminDepartmentId())
            ->get();
        foreach ($adminDepartmentRoles as $adminDepartmentRole) {
            AdminDepartmentRoleDelete::init()
                ->setId(Arr::get($adminDepartmentRoles, 'id'))
                ->run();
        }
        $adminRoleIds = $this->getAdminRoleIds();
        if (!empty($adminRoleIds)) {
            foreach ($adminRoleIds as $adminRoleId) {
                AdminDepartmentRoleAdd::init()
                    ->setAdminDepartmentId($this->getAdminDepartmentId())
                    ->setAdminRoleId($adminRoleId)
                    ->run();
            }
        }
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
     * @return $this
     */
    public function setAdminDepartmentId($adminDepartmentId)
    {
        $this->adminDepartmentId = $adminDepartmentId;
        return $this;
    }

    protected $adminRoleIds;

    /**
     * @return mixed
     */
    public function getAdminRoleIds()
    {
        return $this->adminRoleIds;
    }

    /**
     * @param mixed $adminRoleIds
     * @return $this
     */
    public function setAdminRoleIds($adminRoleIds)
    {
        $this->adminRoleIds = $adminRoleIds;
        return $this;
    }
}
