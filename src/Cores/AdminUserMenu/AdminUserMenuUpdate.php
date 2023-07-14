<?php

namespace Qz\Admin\Permission\Cores\AdminUserMenu;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserMenu;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AdminUserMenuUpdate extends Core
{
    protected function execute()
    {
        $model = AdminUserMenu::withTrashed()
            ->findOrFail($this->getId());
        $model->fill(Arr::whereNotNull([
            'admin_user_id' => $this->getAdminUserId(),
            'admin_menu_id' => $this->getAdminMenuId(),
        ]));
        $model->save();
        $this->setId($model->getKey());
    }

    protected $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return AdminUserMenuUpdate
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $param
     * @return AdminUserMenuUpdate
     */
    public function setParam($param)
    {
        foreach ($param as $key => $value) {
            $setMethod = 'set' . Str::studly($key);
            if (method_exists($this, $setMethod)) {
                call_user_func([$this, $setMethod], $value);
            }
        }
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
     * @return AdminUserMenuUpdate
     */
    public function setAdminUserId($adminUserId)
    {
        $this->adminUserId = $adminUserId;
        return $this;
    }
    protected $adminMenuId;

    /**
     * @return mixed
     */
    public function getAdminMenuId()
    {
        return $this->adminMenuId;
    }

    /**
     * @param mixed $adminMenuId
     * @return AdminUserMenuUpdate
     */
    public function setAdminMenuId($adminMenuId)
    {
        $this->adminMenuId = $adminMenuId;
        return $this;
    }
}
