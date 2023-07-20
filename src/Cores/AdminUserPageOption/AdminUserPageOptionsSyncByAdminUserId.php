<?php

namespace Qz\Admin\Permission\Cores\AdminUserPageOption;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserPageOption;
use Illuminate\Support\Arr;

class AdminUserPageOptionsSyncByAdminUserId extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminUserId())) {
            return;
        }
        $adminUserPageOptions = $this->getAdminUserPageOptions();
        if (is_null($adminUserPageOptions)) {
            return;
        }
        $deletes = AdminUserPageOption::query()
            ->select('id')
            ->where('admin_user_id', $this->getAdminUserId())
            ->whereNotIn('admin_page_option_id', Arr::pluck($adminUserPageOptions, $this->getAdminPageOptionIdKey()))
            ->get();
        foreach ($deletes as $delete) {
            AdminUserPageOptionDelete::init()
                ->setId(Arr::get($delete, 'id'))
                ->run();
        }
        if (!empty($adminUserPageOptions)) {
            foreach ($adminUserPageOptions as $adminUserPageOption) {
                AdminUserPageOptionAdd::init()
                    ->setAdminPageOptionId(Arr::get($adminUserPageOption, $this->getAdminPageOptionIdKey()))
                    ->setType(Arr::get($adminUserPageOption, 'type'))
                    ->setAdminUserId($this->getAdminUserId())
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
     * @param $adminUserId
     * @return $this
     */
    public function setAdminUserId($adminUserId)
    {
        $this->adminUserId = $adminUserId;
        return $this;
    }

    protected $adminUserPageOptions;

    /**
     * @return mixed
     */
    public function getAdminUserPageOptions()
    {
        return $this->adminUserPageOptions;
    }

    /**
     * @param mixed $adminUserPageOptions
     * @return AdminUserPageOptionsSyncByAdminUserId
     */
    public function setAdminUserPageOptions($adminUserPageOptions)
    {
        $this->adminUserPageOptions = $adminUserPageOptions;
        return $this;
    }

    protected $adminPageOptionIdKey = 'admin_page_option_id';

    /**
     * @return string
     */
    public function getAdminPageOptionIdKey()
    {
        return $this->adminPageOptionIdKey;
    }

    /**
     * @param string $adminPageOptionIdKey
     * @return AdminUserPageOptionsSyncByAdminUserId
     */
    public function setAdminPageOptionIdKey($adminPageOptionIdKey)
    {
        $this->adminPageOptionIdKey = $adminPageOptionIdKey;
        return $this;
    }
}
