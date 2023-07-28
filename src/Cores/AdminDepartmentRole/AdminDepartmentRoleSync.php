<?php
namespace Qz\Admin\Permission\Cores\AdminDepartmentRole;

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
        // 删除多余数据
        AdminDepartmentRole::query()
            ->where('admin_department_id', $this->getAdminDepartmentId())
            ->whereNotIn('admin_role_id', $this->getAdminRoleIds())
            ->delete();
        // 恢复已删除数据
        AdminDepartmentRole::onlyTrashed()
            ->where('admin_department_id', $this->getAdminDepartmentId())
            ->whereIn('admin_role_id', $this->getAdminRoleIds())
            ->restore();
        // 添加新数据
        $oldIds = AdminDepartmentRole::query()
            ->where('admin_department_id', $this->getAdminDepartmentId())
            ->pluck('admin_role_id')
            ->toArray();
        $addIds = array_diff($this->getAdminRoleIds(), $oldIds);
        foreach ($addIds as $addId) {
            AdminDepartmentRole::query()->create([
                'admin_role_id' => $addId,
                'admin_department_id' => $this->getAdminDepartmentId(),
            ]);
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
