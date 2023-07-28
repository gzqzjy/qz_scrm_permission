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
        $ids = $this->getAdminPageColumnIds();
        if (is_null($ids)) {
            return;
        }
        AdminRolePageColumn::query()
            ->where('admin_role_id', $this->getAdminRoleId())
            ->whereNotIn('admin_page_column_id', $ids)
            ->delete();
        if (!empty($ids)) {
            foreach ($ids as $id) {
                AdminRolePageColumnAdd::init()
                    ->setAdminRoleId($this->getAdminRoleId())
                    ->setAdminPageColumnId($id)
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
