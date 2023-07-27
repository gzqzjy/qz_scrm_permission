<?php
namespace Qz\Admin\Permission\Cores\AdminRolePageOption;

use Illuminate\Support\Arr;
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
        $deleteIds = AdminRolePageOption::query()
            ->select(['id'])
            ->where('admin_role_id', $this->getAdminRoleId())
            ->where('admin_role_id', '>', 0)
            ->pluck('id')
            ->toArray();
        if (!empty($deleteIds)) {
            foreach ($deleteIds as $deleteId) {
                AdminRolePageOptionDelete::init()
                    ->setId($deleteId)
                    ->run();
            }
        }
        $ids = $this->getAdminPageOptionIds();
        if (!empty($ids)) {
            foreach ($ids as $id) {
                AdminRolePageOptionAdd::init()
                    ->setAdminRoleId($this->getAdminRoleId())
                    ->setAdminPageOptionId($id)
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
