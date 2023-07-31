<?php
namespace Qz\Admin\Permission\Cores\AdminRolePageColumn;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminRolePageColumn;

class AdminRolePageColumnSync extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminRoleId())) {
            return;
        }
        if (is_null($this->getAdminPageColumnIds())) {
            return;
        }
        AdminRolePageColumn::query()
            ->where('admin_role_id', $this->getAdminRoleId())
            ->whereNotIn('admin_menu_id', $this->getAdminPageColumnIds())
            ->delete();
        AdminRolePageColumn::onlyTrashed()
            ->where('admin_role_id', $this->getAdminRoleId())
            ->whereIn('admin_menu_id', $this->getAdminPageColumnIds())
            ->restore();
        $oldIds = AdminRolePageColumn::query()
            ->where('admin_role_id', $this->getAdminRoleId())
            ->pluck('admin_menu_id')
            ->toArray();
        $addIds = array_diff($this->getAdminPageColumnIds(), $oldIds);
        foreach ($addIds as $addId) {
            AdminRolePageColumn::query()->create([
                'admin_role_id' => $this->getAdminRoleId(),
                'admin_menu_id' => $addId,
            ]);
        }
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
     * @return AdminRolePageColumnSync
     */
    public function setAdminRoleId($adminRoleId)
    {
        $this->adminRoleId = $adminRoleId;
        return $this;
    }

    protected $adminPageColumnIds;

    /**
     * @return mixed
     */
    public function getAdminPageColumnIds()
    {
        return $this->adminPageColumnIds;
    }

    /**
     * @param mixed $adminPageColumnIds
     * @return AdminRolePageColumnSync
     */
    public function setAdminPageColumnIds($adminPageColumnIds)
    {
        $this->adminPageColumnIds = $adminPageColumnIds;
        return $this;
    }
}
