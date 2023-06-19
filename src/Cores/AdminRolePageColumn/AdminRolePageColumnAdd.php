<?php
namespace Qz\Admin\Permission\Cores\AdminRolePageColumn;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminRolePageColumn;

class AdminRolePageColumnAdd extends Core
{
    protected function execute()
    {
        $model = AdminRolePageColumn::withTrashed()
            ->firstOrCreate(Arr::whereNotNull([
                'admin_role_id' => $this->getAdminRoleId(),
                'admin_page_column_id' => $this->getAdminPageColumnId(),
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
     * @return AdminRolePageColumnAdd
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $param
     * @return AdminRolePageColumnAdd
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
     * @return AdminRolePageColumnAdd
     */
    public function setAdminRoleId($adminRoleId)
    {
        $this->adminRoleId = $adminRoleId;
        return $this;
    }

    protected $adminPageColumnId;

    /**
     * @return mixed
     */
    public function getAdminPageColumnId()
    {
        return $this->adminPageColumnId;
    }

    /**
     * @param mixed $adminPageColumnId
     * @return AdminRolePageColumnAdd
     */
    public function setAdminPageColumnId($adminPageColumnId)
    {
        $this->adminPageColumnId = $adminPageColumnId;
        return $this;
    }



}
