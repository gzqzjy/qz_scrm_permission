<?php
namespace Qz\Admin\Permission\Cores\AdminRoleMenu;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminRoleMenu;

class AdminRoleMenuAdd extends Core
{
    protected function execute()
    {
        $model = AdminRoleMenu::withTrashed()
            ->firstOrCreate(Arr::whereNotNull([
                'admin_role_id' => $this->getAdminRoleId(),
                'admin_menu_id' => $this->getAdminMenuId(),
            ]));
        if ($model->trashed()) {
            $model->restore();
        }
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
     * @return AdminRoleMenuAdd
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $param
     * @return AdminRoleMenuAdd
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
     * @return AdminRoleMenuAdd
     */
    public function setAdminRoleId($adminRoleId)
    {
        $this->adminRoleId = $adminRoleId;
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
     * @return AdminRoleMenuAdd
     */
    public function setAdminMenuId($adminMenuId)
    {
        $this->adminMenuId = $adminMenuId;
        return $this;
    }

}
