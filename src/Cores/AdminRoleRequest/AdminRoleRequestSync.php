<?php
namespace Qz\Admin\Permission\Cores\AdminRoleRequest;

use Illuminate\Support\Arr;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminRoleRequest;

class AdminRoleRequestSync extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminRoleId())) {
            return;
        }
        if (is_null($this->getAdminRequestIds())) {
            return;
        }
        $deleteIds = AdminRoleRequest::query()
            ->select(['id'])
            ->where('admin_role_id', $this->getAdminRoleId())
            ->where('admin_role_id', '>', 0)
            ->pluck('id')
            ->toArray();
        if (!empty($deleteIds)) {
            foreach ($deleteIds as $deleteId) {
                AdminRoleRequestDelete::init()
                    ->setId($deleteId)
                    ->run();
            }
        }
        $ids = $this->getAdminRequestIds();
        if (!empty($ids)) {
            foreach ($ids as $id) {
                AdminRoleRequestAdd::init()
                    ->setAdminRoleId($this->getAdminRoleId())
                    ->setAdminRequestId($id)
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
     * @return AdminRoleRequestSync
     */
    public function setAdminRoleId($adminRoleId)
    {
        $this->adminRoleId = $adminRoleId;
        return $this;
    }

    protected $adminRequestIds;

    /**
     * @return mixed
     */
    public function getAdminRequestIds()
    {
        return $this->adminRequestIds;
    }

    /**
     * @param mixed $adminRequestIds
     * @return AdminRoleRequestSync
     */
    public function setAdminRequestIds($adminRequestIds)
    {
        $this->adminRequestIds = $adminRequestIds;
        return $this;
    }
}
