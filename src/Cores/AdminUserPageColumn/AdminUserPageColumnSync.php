<?php

namespace Qz\Admin\Permission\Cores\AdminUserPageColumn;

use Qz\Admin\Permission\Cores\AdminUser\AdminPageColumnIdsByAdminUserIdGet;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserPageColumn;

class AdminUserPageColumnSync extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminUserId())) {
            return;
        }
        $adminPageColumnIds = $this->getAdminPageColumnIds();
        if (is_null($adminPageColumnIds)) {
            return;
        }
        AdminUserPageColumn::query()
            ->where('admin_user_id', $this->getAdminUserId())
            ->whereNotIn('admin_page_column_id', $adminPageColumnIds)
            ->delete();
        $oldAdminPageColumnIds = AdminPageColumnIdsByAdminUserIdGet::init()
            ->setAdminUserId($this->getAdminUserId())
            ->run()
            ->getAdminPageColumnIds();
        $addIds = array_diff($adminPageColumnIds, $oldAdminPageColumnIds);
        foreach ($addIds as $addId) {
            AdminUserPageColumnAdd::init()
                ->setAdminPageColumnId($addId)
                ->setAdminUserId($this->getAdminUserId())
                ->setType(AdminUserPageColumn::TYPE_ADD)
                ->run();
        }
        $deleteIds = array_diff($oldAdminPageColumnIds, $adminPageColumnIds);
        foreach ($deleteIds as $deleteId) {
            AdminUserPageColumnAdd::init()
                ->setAdminPageColumnId($deleteId)
                ->setAdminUserId($this->getAdminUserId())
                ->setType(AdminUserPageColumn::TYPE_DELETE)
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
     * @return AdminUserPageColumnSync
     */
    public function setAdminPageColumnIds($adminPageColumnIds)
    {
        $this->adminPageColumnIds = $adminPageColumnIds;
        return $this;
    }
}
