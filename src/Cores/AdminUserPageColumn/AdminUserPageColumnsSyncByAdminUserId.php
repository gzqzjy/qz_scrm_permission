<?php

namespace Qz\Admin\Permission\Cores\AdminUserPageColumn;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserPageColumn;
use Illuminate\Support\Arr;

class AdminUserPageColumnsSyncByAdminUserId extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminUserId())) {
            return;
        }
        $adminUserPageColumns = $this->getAdminUserPageColumns();
        if (is_null($adminUserPageColumns)) {
            return;
        }
        $deletes = AdminUserPageColumn::query()
            ->select('id')
            ->where('admin_user_id', $this->getAdminUserId())
            ->whereNotIn('admin_page_column_id', Arr::pluck($adminUserPageColumns, $this->getAdminPageColumnIdKey()))
            ->get();
        foreach ($deletes as $delete) {
            AdminUserPageColumnDelete::init()
                ->setId(Arr::get($delete, 'id'))
                ->run();
        }
        if (!empty($adminUserPageColumns)) {
            foreach ($adminUserPageColumns as $adminUserPageColumn) {
                AdminUserPageColumnAdd::init()
                    ->setAdminPageColumnId(Arr::get($adminUserPageColumn, $this->getAdminPageColumnIdKey()))
                    ->setType(Arr::get($adminUserPageColumn, 'type'))
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

    protected $adminUserPageColumns;

    /**
     * @return mixed
     */
    public function getAdminUserPageColumns()
    {
        return $this->adminUserPageColumns;
    }

    /**
     * @param mixed $adminUserPageColumns
     * @return AdminUserPageColumnsSyncByAdminUserId
     */
    public function setAdminUserPageColumns($adminUserPageColumns)
    {
        $this->adminUserPageColumns = $adminUserPageColumns;
        return $this;
    }

    protected $adminPageColumnIdKey = 'admin_page_column_id';

    /**
     * @return string
     */
    public function getAdminPageColumnIdKey()
    {
        return $this->adminPageColumnIdKey;
    }

    /**
     * @param string $adminPageColumnIdKey
     * @return AdminUserPageColumnsSyncByAdminUserId
     */
    public function setAdminPageColumnIdKey($adminPageColumnIdKey)
    {
        $this->adminPageColumnIdKey = $adminPageColumnIdKey;
        return $this;
    }
}
