<?php
namespace Qz\Admin\Permission\Cores\AdminRoleMenu;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminRoleMenu;

class AdminRoleMenuSync extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminRoleId())) {
            return;
        }
        if (is_null($this->getAdminMenuIds())) {
            return;
        }
        AdminRoleMenu::query()
            ->where('admin_role_id', $this->getAdminRoleId())
            ->whereNotIn('admin_menu_id', $this->getAdminMenuIds())
            ->delete();
        AdminRoleMenu::onlyTrashed()
            ->where('admin_role_id', $this->getAdminRoleId())
            ->whereIn('admin_menu_id', $this->getAdminMenuIds())
            ->restore();
        $oldIds = AdminRoleMenu::query()
            ->where('admin_role_id', $this->getAdminRoleId())
            ->pluck('admin_menu_id')
            ->toArray();
        $addIds = array_diff($this->getAdminMenuIds(), $oldIds);
        foreach ($addIds as $addId) {
            AdminRoleMenu::query()->create([
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
     * @return AdminRoleMenuSync
     */
    public function setAdminRoleId($adminRoleId)
    {
        $this->adminRoleId = $adminRoleId;
        return $this;
    }

    protected $adminMenuIds;

    /**
     * @return mixed
     */
    public function getAdminMenuIds()
    {
        return $this->adminMenuIds;
    }

    /**
     * @param mixed $adminMenuIds
     * @return AdminRoleMenuSync
     */
    public function setAdminMenuIds($adminMenuIds)
    {
        $this->adminMenuIds = $adminMenuIds;
        return $this;
    }
}
