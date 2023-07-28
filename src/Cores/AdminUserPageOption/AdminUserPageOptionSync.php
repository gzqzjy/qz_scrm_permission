<?php

namespace Qz\Admin\Permission\Cores\AdminUserPageOption;

use Qz\Admin\Permission\Cores\AdminUser\AdminPageOptionIdsByAdminUserIdGet;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserPageOption;

class AdminUserPageOptionSync extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminUserId())) {
            return;
        }
        $adminPageOptionIds = $this->getAdminPageOptionIds();
        if (is_null($adminPageOptionIds)) {
            return;
        }
        AdminUserPageOption::query()
            ->where('admin_user_id', $this->getAdminUserId())
            ->whereNotIn('admin_page_option_id', $adminPageOptionIds)
            ->delete();
        $oldAdminPageOptionIds = AdminPageOptionIdsByAdminUserIdGet::init()
            ->setAdminUserId($this->getAdminUserId())
            ->run()
            ->getAdminPageOptionIds();
        $addIds = array_diff($adminPageOptionIds, $oldAdminPageOptionIds);
        foreach ($addIds as $addId) {
            AdminUserPageOptionAdd::init()
                ->setAdminPageOptionId($addId)
                ->setAdminUserId($this->getAdminUserId())
                ->setType(AdminUserPageOption::TYPE_ADD)
                ->run();
        }
        $deleteIds = array_diff($oldAdminPageOptionIds, $adminPageOptionIds);
        foreach ($deleteIds as $deleteId) {
            AdminUserPageOptionAdd::init()
                ->setAdminPageOptionId($deleteId)
                ->setAdminUserId($this->getAdminUserId())
                ->setType(AdminUserPageOption::TYPE_DELETE)
                ->run();
        }
    }

    protected $adminUserId;

    /**
     * @return mixed
     */
    public function getAdminUserId()
    {
        return $this->adminUserId;
    }

    /**
     * @param $adminUserId
     * @return $this
     */
    public function setAdminUserId($adminUserId)
    {
        $this->adminUserId = $adminUserId;
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
     * @return AdminUserPageOptionSync
     */
    public function setAdminPageOptionIds($adminPageOptionIds)
    {
        $this->adminPageOptionIds = $adminPageOptionIds;
        return $this;
    }
}
