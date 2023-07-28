<?php

namespace Qz\Admin\Permission\Cores\AdminUser;

use Illuminate\Support\Arr;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUser;
use Qz\Admin\Permission\Models\AdminUserPageColumn;

class AdminPageColumnIdsByAdminUserIdGet extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminUserId())) {
            return;
        }
        $model = AdminUser::query()
            ->select(['id'])
            ->find($this->getAdminUserId());
        if (empty($model)) {
            return;
        }
        $model->load([
            'adminUserRoles',
            'adminUserRoles.adminRole',
            'adminUserRoles.adminRole.adminRolePageColumns',
            'adminUserPageColumns',
        ]);
        $adminUserRoles = Arr::get($model, 'adminUserRoles');
        foreach ($adminUserRoles as $adminUserRole) {
            $adminRole = Arr::get($adminUserRole, 'adminRole');
            if (empty($adminRole)) {
                continue;
            }
            $adminRolePageColumns = Arr::get($adminRole, 'adminRolePageColumns');
            foreach ($adminRolePageColumns as $adminRolePageColumn) {
                $this->adminPageColumnIds[] = Arr::get($adminRolePageColumn, 'admin_page_column_id');
            }
        }
        $adminUserPageColumns = Arr::get($model, 'adminUserPageColumns');
        foreach ($adminUserPageColumns as $adminUserPageColumn) {
            if (Arr::get($adminUserPageColumn, 'type') != AdminUserPageColumn::TYPE_DELETE) {
                $this->adminPageColumnIds[] = Arr::get($adminUserPageColumn, 'admin_page_column_id');
            } else {
                $this->adminPageColumnIds = Arr::where($this->adminPageColumnIds, function ($adminPageColumnId) use ($adminUserPageColumn) {
                    return $adminPageColumnId != Arr::get($adminUserPageColumn, 'admin_page_column_id');
                });
            }
        }
        $this->adminPageColumnIds = array_unique(array_values($this->adminPageColumnIds));
    }

    protected $adminPageColumnIds = [];

    /**
     * @return mixed
     */
    public function getAdminPageColumnIds()
    {
        return $this->adminPageColumnIds;
    }

    /**
     * @param mixed $adminPageColumnIds
     * @return $this
     */
    public function setAdminPageColumnIds($adminPageColumnIds)
    {
        $this->adminPageColumnIds = $adminPageColumnIds;
        return $this;
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
     * @return AdminPageColumnIdsByAdminUserIdGet
     */
    public function setAdminUserId($adminUserId)
    {
        $this->adminUserId = $adminUserId;
        return $this;
    }
}
