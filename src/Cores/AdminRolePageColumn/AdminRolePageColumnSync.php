<?php
namespace Qz\Admin\Permission\Cores\AdminRolePageColumn;

use Illuminate\Support\Arr;
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
        $deleteIds = AdminRolePageColumn::query()
            ->where('admin_role_id', $this->getAdminRoleId())
            ->where('admin_role_id', '>', 0)
            ->pluck('id')
            ->toArray();
        if (!empty($deleteIds)) {
            foreach ($deleteIds as $deleteId) {
                AdminRolePageColumnDelete::init()
                    ->setId($deleteId)
                    ->run();
            }
        }
        $ids = $this->getAdminPageColumnIds();
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
