<?php
namespace Qz\Admin\Permission\Cores\AdminRoleMenu;

use Illuminate\Support\Arr;
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
        $deleteIds = AdminRoleMenu::query()
            ->where('admin_role_id', $this->getAdminRoleId())
            ->where('admin_role_id', '>', 0)
            ->whereNotIn('admin_menu_id', $this->getAdminMenuIds())
            ->pluck('id')
            ->toArray();
        if (!empty($deleteIds)) {
            foreach ($deleteIds as $deleteId) {
                AdminRoleMenuDelete::init()
                    ->setId($deleteId)
                    ->run();
            }
        }
        $ids = $this->getAdminMenuIds();
        if (!empty($ids)) {
            foreach ($ids as $id) {
                AdminRoleMenuAdd::init()
                    ->setAdminRoleId($this->getAdminRoleId())
                    ->setAdminMenuId($id)
                    ->run();
            }
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
