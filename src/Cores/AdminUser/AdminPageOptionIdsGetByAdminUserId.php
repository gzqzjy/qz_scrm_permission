<?php

namespace Qz\Admin\Permission\Cores\AdminUser;

use Illuminate\Support\Arr;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserPageOption;
use Qz\Admin\Permission\Models\AdminUserRole;

class AdminPageOptionIdsGetByAdminUserId extends Core
{
    protected function execute()
    {
        $adminUserRoles = AdminUserRole::query()
            ->where('admin_user_id', $this->getAdminUserId())
            ->get();
        $adminUserRoles->load([
            'adminRole',
            'adminRole.adminRolePageOptions',
        ]);
        foreach ($adminUserRoles as $adminUserRole) {
            $adminRole = $adminUserRole->adminRole;
            if (empty($adminRole)) {
                continue;
            }
            $adminRolePageOptions = $adminRole->adminRolePageOptions;
            foreach ($adminRolePageOptions as $adminRolePageOption) {
                $this->id[] = Arr::get($adminRolePageOption, 'admin_page_option_id');
            }
        }
        $adminUserPageOptionIds = AdminUserPageOption::query()
            ->select(['type', 'admin_page_option_id'])
            ->where('admin_user_id', $this->getAdminUserId())
            ->select(['type', 'admin_page_option_id'])
            ->get();
        $adminUserPageOptionIds = $adminUserPageOptionIds->groupBy('type')->toArray();
        if ($adminPageOptionAdd = Arr::get($adminUserPageOptionIds, 'add')) {
            $this->id = array_unique(array_merge($this->id, Arr::pluck($adminPageOptionAdd, 'admin_page_option_id')));
        }
        if ($adminPageOptionDelete = Arr::get($adminUserPageOptionIds, 'delete')) {
            $this->id = array_diff($this->id, Arr::pluck($adminPageOptionDelete, 'admin_page_option_id'));
        }
    }

    protected $id = [];

    /**
     * @return array
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param array $id
     * @return AdminPageOptionIdsGetByAdminUserId
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @return AdminPageOptionIdsGetByAdminUserId
     */
    public function setAdminUserId($adminUserId)
    {
        $this->adminUserId = $adminUserId;
        return $this;
    }
}
