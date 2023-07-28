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
            ->whereNotIn('admin_page_option_id', $this->getAdminPageOptionIds())
            ->delete();
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
