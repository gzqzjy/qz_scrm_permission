<?php
namespace Qz\Admin\Permission\Cores\AdminUserRequestEmployee;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserRequestEmployee;

class AdminUserRequestEmployeeAdd extends Core
{
    protected function execute()
    {
        $model = AdminUserRequestEmployee::withTrashed()
            ->updateOrCreate(Arr::whereNotNull([
                'admin_user_id' => $this->getAdminUserId(),
                'admin_request_id' => $this->getAdminRequestId(),
                'permission_admin_user_id' => $this->getPermissionAdminUserId(),
            ]), Arr::whereNotNull([
                'type' => $this->getType()
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
     * @return AdminUserRequestEmployeeAdd
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $param
     * @return AdminUserRequestEmployeeAdd
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

    protected $adminRequestId;

    /**
     * @return mixed
     */
    public function getAdminRequestId()
    {
        return $this->adminRequestId;
    }

    /**
     * @param mixed $adminRequestId
     * @return AdminUserRequestEmployeeAdd
     */
    public function setAdminRequestId($adminRequestId)
    {
        $this->adminRequestId = $adminRequestId;
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
     * @return AdminUserRequestEmployeeAdd
     */
    public function setAdminUserId($adminUserId)
    {
        $this->adminUserId = $adminUserId;
        return $this;
    }

    protected $permissionAdminUserId;

    /**
     * @return mixed
     */
    public function getPermissionAdminUserId()
    {
        return $this->permissionAdminUserId;
    }

    /**
     * @param mixed $permissionAdminUserId
     * @return AdminUserRequestEmployeeAdd
     */
    public function setPermissionAdminUserId($permissionAdminUserId)
    {
        $this->permissionAdminUserId = $permissionAdminUserId;
        return $this;
    }



    protected $type;

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return AdminUserRequestEmployeeAdd
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

}
