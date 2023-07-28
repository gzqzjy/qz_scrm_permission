<?php

namespace Qz\Admin\Permission\Cores\AdminUser;

use Illuminate\Support\Arr;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUser;
use Qz\Admin\Permission\Models\AdminUserPageOption;

class AdminPageOptionIdsByAdminUserIdGet extends Core
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
            'adminUserRoles.adminRole.adminRolePageOptions',
            'adminUserPageOptions',
        ]);
        $adminUserRoles = Arr::get($model, 'adminUserRoles');
        foreach ($adminUserRoles as $adminUserRole) {
            $adminRole = Arr::get($adminUserRole, 'adminRole');
            if (empty($adminRole)) {
                continue;
            }
            $adminRolePageOptions = Arr::get($adminRole, 'adminRolePageOptions');
            foreach ($adminRolePageOptions as $adminRolePageOption) {
                $this->adminPageOptionIds[] = Arr::get($adminRolePageOption, 'admin_page_option_id');
            }
        }
        $adminUserPageOptions = Arr::get($model, 'adminUserPageOptions');
        foreach ($adminUserPageOptions as $adminUserPageOption) {
            if (Arr::get($adminUserPageOption, 'type') != AdminUserPageOption::TYPE_DELETE) {
                $this->adminPageOptionIds[] = Arr::get($adminUserPageOption, 'admin_page_option_id');
            } else {
                $this->adminPageOptionIds = Arr::where($this->adminPageOptionIds, function ($adminPageOptionId) use ($adminUserPageOption) {
                    return $adminPageOptionId != Arr::get($adminUserPageOption, 'admin_page_option_id');
                });
            }
        }
        $this->adminPageOptionIds = array_unique(array_values($this->adminPageOptionIds));
    }
    protected $adminPageOptionIds = [];

    /**
     * @return mixed
     */
    public function getAdminPageOptionIds()
    {
        return $this->adminPageOptionIds;
    }

    /**
     * @param mixed $adminPageOptionIds
     * @return $this
     */
    public function setAdminPageOptionIds($adminPageOptionIds)
    {
        $this->adminPageOptionIds = $adminPageOptionIds;
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
     * @return AdminPageOptionIdsByAdminUserIdGet
     */
    public function setAdminUserId($adminUserId)
    {
        $this->adminUserId = $adminUserId;
        return $this;
    }
}
