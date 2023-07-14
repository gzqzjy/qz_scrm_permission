<?php
namespace Qz\Admin\Permission\Cores\AdminUserRole;

use Illuminate\Support\Arr;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserRole;

class AdminUserRoleSync extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminUserId())) {
            return;
        }
        if (is_null($this->getAdminRoleIds())) {
            return;
        }
        $adminUserRoles = AdminUserRole::query()
            ->select(['id'])
            ->where('admin_user_id', $this->getAdminUserId())
            ->get();
        foreach ($adminUserRoles as $adminUserRole) {
            AdminUserRoleDelete::init()
                ->setId(Arr::get($adminUserRole, 'id'))
                ->run();
        }
        $adminRoleIds = $this->getAdminRoleIds();
        if (!empty($adminRoleIds)) {
            foreach ($adminRoleIds as $adminRoleId) {
                AdminUserRoleAdd::init()
                    ->setAdminUserId($this->getAdminUserId())
                    ->setAdminRoleId($adminRoleId)
                    ->run();
            }
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
     * @param mixed $adminUserId
     * @return AdminUserRoleSync
     */
    public function setAdminUserId($adminUserId)
    {
        $this->adminUserId = $adminUserId;
        return $this;
    }

    protected $adminRoleIds;

    /**
     * @return mixed
     */
    public function getAdminRoleIds()
    {
        return $this->adminRoleIds;
    }

    /**
     * @param mixed $adminRoleIds
     * @return AdminUserRoleSync
     */
    public function setAdminRoleIds($adminRoleIds)
    {
        $this->adminRoleIds = $adminRoleIds;
        return $this;
    }
}
