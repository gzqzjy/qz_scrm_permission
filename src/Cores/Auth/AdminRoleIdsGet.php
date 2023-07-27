<?php

namespace Qz\Admin\Permission\Cores\Auth;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminRole;
use Qz\Admin\Permission\Models\AdminUser;
use Qz\Admin\Permission\Models\AdminUserRole;

class AdminRoleIdsGet extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminUserId())) {
            return;
        }
        $adminUser = AdminUser::query()
            ->select(['id', 'customer_id'])
            ->find($this->getAdminUserId());
        if (empty($adminUser)) {
            return;
        }
        $adminUser->load('administrator');
        if (Arr::get($adminUser, 'administrator.id')) {
            $ids = AdminRole::query()
                ->where('customer_id', Arr::get($adminUser, 'customer_id'))
                ->pluck('id')
                ->toArray();
            if (!empty($ids)) {
                $this->ids = array_merge($this->ids, $ids);
            }
            return;
        }
        $ids = AdminUserRole::query()
            ->where('admin_user_id', $this->getAdminUserId())
            ->pluck('admin_role_id')
            ->toArray();
        if (!empty($ids)) {
            $this->ids = array_merge($this->ids, $ids);
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
     * @return AdminRoleIdsGet
     */
    public function setAdminUserId($adminUserId)
    {
        $this->adminUserId = $adminUserId;
        return $this;
    }

    protected $ids = [];

    /**
     * @return array
     */
    public function getIds()
    {
        return $this->ids;
    }

    /**
     * @param array $ids
     * @return AdminRoleIdsGet
     */
    public function setIds($ids)
    {
        $this->ids = $ids;
        return $this;
    }
}
