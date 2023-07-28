<?php

namespace Qz\Admin\Permission\Cores\AdminRole;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Qz\Admin\Permission\Cores\AdminRoleMenu\AdminRoleMenuSync;
use Qz\Admin\Permission\Cores\AdminRolePageColumn\AdminRolePageColumnSync;
use Qz\Admin\Permission\Cores\AdminRolePageOption\AdminRolePageOptionSync;
use Qz\Admin\Permission\Cores\AdminRoleRequest\AdminRoleRequestSync;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminRole;

class AdminRoleAdd extends Core
{
    protected function execute()
    {
        $model = AdminRole::withTrashed()
            ->firstOrCreate(Arr::whereNotNull([
                'name' => $this->getName(),
                'admin_role_group_id' => $this->getAdminRoleGroupId(),
                'customer_id' => $this->getCustomerId(),
            ]));
        if ($model->trashed()) {
            $model->restore();
        }
        $this->setId($model->getKey());
        AdminRoleMenuSync::init()
            ->setAdminRoleId($this->getId())
            ->setAdminMenuIds($this->getAdminMenuIds())
            ->run();
        AdminRolePageColumnSync::init()
            ->setAdminRoleId($this->getId())
            ->setAdminPageColumnIds($this->getAdminPageColumnIds())
            ->run();
        AdminRolePageOptionSync::init()
            ->setAdminRoleId($this->getId())
            ->setAdminPageOptionIds($this->getAdminPageOptionIds())
            ->run();
        AdminRoleRequestSync::init()
            ->setAdminRoleId($this->getId())
            ->setAdminRequests($this->getAdminRequests())
            ->run();
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
     * @return AdminRoleAdd
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $param
     * @return AdminRoleAdd
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

    protected $name;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return AdminRoleAdd
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    protected $adminRoleGroupId;

    /**
     * @return mixed
     */
    public function getAdminRoleGroupId()
    {
        return $this->adminRoleGroupId;
    }

    /**
     * @param mixed $adminRoleGroupId
     * @return AdminRoleAdd
     */
    public function setAdminRoleGroupId($adminRoleGroupId)
    {
        $this->adminRoleGroupId = $adminRoleGroupId;
        return $this;
    }

    protected $customerId;

    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param mixed $customerId
     * @return AdminRoleAdd
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
        return $this;
    }

    protected $adminMenuIds;

    /**
     * @return mixed
     */
    public function getAdminMenuIds()
    {
        return $this->adminMenuIds;
    }

    /**
     * @param mixed $adminMenuIds
     * @return AdminRoleAdd
     */
    public function setAdminMenuIds($adminMenuIds)
    {
        $this->adminMenuIds = $adminMenuIds;
        return $this;
    }

    protected $adminPageOptionIds;

    /**
     * @return mixed
     */
    public function getAdminPageOptionIds()
    {
        return $this->adminPageOptionIds;
    }

    /**
     * @param mixed $adminPageOptionIds
     * @return AdminRoleAdd
     */
    public function setAdminPageOptionIds($adminPageOptionIds)
    {
        $this->adminPageOptionIds = $adminPageOptionIds;
        return $this;
    }

    protected $adminPageColumnIds;

    /**
     * @return mixed
     */
    public function getAdminPageColumnIds()
    {
        return $this->adminPageColumnIds;
    }

    /**
     * @param mixed $adminPageColumnIds
     * @return AdminRoleAdd
     */
    public function setAdminPageColumnIds($adminPageColumnIds)
    {
        $this->adminPageColumnIds = $adminPageColumnIds;
        return $this;
    }
    
    protected $adminRequests;

    /**
     * @return mixed
     */
    public function getAdminRequests()
    {
        return $this->adminRequests;
    }

    /**
     * @param mixed $adminRequests
     * @return AdminRoleAdd
     */
    public function setAdminRequests($adminRequests)
    {
        $this->adminRequests = $adminRequests;
        return $this;
    }
}
