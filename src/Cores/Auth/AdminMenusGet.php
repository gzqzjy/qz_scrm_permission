<?php

namespace Qz\Admin\Permission\Cores\Auth;

use Illuminate\Database\Eloquent\Builder;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminMenu;

class AdminMenusGet extends Core
{
    protected function execute()
    {
        AdminMenu::query()
            ->where(function (Builder $builder) {
                $builder->whereHas('adminUserMenus', function (Builder $builder) {
                    $builder->where('admin_user_id', $this->getAdminUserId());
                })->orWhere('adminRoleMenus', function (Builder $builder) {
                    $builder->whereHas('adminRole', function (Builder $builder) {
                        $builder->whereHas('adminUserRoles', function (Builder $builder) {
                            $builder->where('admin_user_id', $this->getAdminUserId());
                        });
                    });
                });
            });
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
     * @return AdminMenusGet
     */
    public function setAdminUserId($adminUserId)
    {
        $this->adminUserId = $adminUserId;
        return $this;
    }
}
