<?php
namespace Qz\Admin\Permission\Cores\AdminRolePageOption;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminRolePageOption;

class AdminRolePageOptionSync extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminRoleId())) {
            return;
        }
        if (is_null($this->getAdminPageOptionIds())) {
            return;
        }
        AdminRolePageOption::query()
            ->where('admin_role_id', $this->getAdminRoleId())
            ->whereNotIn('admin_menu_id', $this->getAdminPageOptionIds())
            ->delete();
        AdminRolePageOption::onlyTrashed()
            ->where('admin_role_id', $this->getAdminRoleId())
            ->whereIn('admin_menu_id', $this->getAdminPageOptionIds())
            ->restore();
        $oldIds = AdminRolePageOption::query()
            ->where('admin_role_id', $this->getAdminRoleId())
            ->pluck('admin_menu_id')
            ->toArray();
        $addIds = array_diff($this->getAdminPageOptionIds(), $oldIds);
        foreach ($addIds as $addId) {
            AdminRolePageOption::query()->create([
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
     * @return AdminRolePageOptionSync
     */
    public function setAdminRoleId($adminRoleId)
    {
        $this->adminRoleId = $adminRoleId;
        return $this;
    }

    protected $adminPageOptionIds;

    /**
     * @return mixed
     */
    public function getAdminPageOptionIds()
    {
        return $this->adminPageOptionIds;
    }

    /**
     * @param mixed $adminPageOptionIds
     * @return AdminRolePageOptionSync
     */
    public function setAdminPageOptionIds($adminPageOptionIds)
    {
        $this->adminPageOptionIds = $adminPageOptionIds;
        return $this;
    }
}
