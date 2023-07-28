<?php

namespace Qz\Admin\Permission\Cores\AdminUser;

use Qz\Admin\Permission\Cores\AdminUserDepartment\AdminUserDepartmentSync;
use Qz\Admin\Permission\Cores\AdminUserMenu\AdminUserMenuSync;
use Qz\Admin\Permission\Cores\AdminUserPageColumn\AdminUserPageColumnSync;
use Qz\Admin\Permission\Cores\AdminUserPageOption\AdminUserPageOptionSync;
use Qz\Admin\Permission\Cores\AdminUserRequest\AdminUserRequestSync;
use Qz\Admin\Permission\Cores\AdminUserRole\AdminUserRoleSync;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUser;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AdminUserAdd extends Core
{
    protected function execute()
    {
        $model = AdminUser::withTrashed()
            ->updateOrCreate(Arr::whereNotNull([
                'mobile' => $this->getMobile(),
                'customer_id' => $this->getCustomerId(),
            ]), Arr::whereNotNull([
                'name' => $this->getName(),
                'status' => $this->getStatus(),
                'sex' => $this->getSex(),
            ]));
        if ($model->trashed()) {
            $model->restore();
        }
        $this->setId($model->getKey());
        AdminUserRoleSync::init()
            ->setAdminUserId($this->getId())
            ->setAdminRoleIds($this->getAdminRoleIds())
            ->run();
        AdminUserDepartmentSync::init()
            ->setAdminUserId($this->getId())
            ->setAdminUserDepartments($this->getAdminUserDepartments())
            ->run();
        AdminUserMenuSync::init()
            ->setAdminUserId($this->getId())
            ->setAdminMenuIds($this->getAdminMenuIds())
            ->run();
        AdminUserPageColumnSync::init()
            ->setAdminUserId($this->getId())
            ->setAdminPageColumnIds($this->getAdminPageColumnIds())
            ->run();
        AdminUserPageOptionSync::init()
            ->setAdminUserId($this->getId())
            ->setAdminPageOptionIds($this->getAdminPageOptionIds())
            ->run();
        AdminUserRequestSync::init()
            ->setAdminUserId($this->getId())
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
     * @return AdminUserAdd
     */
    protected function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $param
     * @return AdminUserAdd
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
     * @return AdminUserAdd
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    protected $mobile;

    /**
     * @return mixed
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param mixed $mobile
     * @return AdminUserAdd
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
        return $this;
    }

    protected $status;

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     * @return AdminUserAdd
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    protected $sex;

    /**
     * @return mixed
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * @param mixed $sex
     * @return AdminUserAdd
     */
    public function setSex($sex)
    {
        $this->sex = $sex;
        return $this;
    }

    protected $adminUserDepartments;

    /**
     * @return mixed
     */
    public function getAdminUserDepartments()
    {
        return $this->adminUserDepartments;
    }

    /**
     * @param mixed $adminUserDepartments
     * @return AdminUserAdd
     */
    public function setAdminUserDepartments($adminUserDepartments)
    {
        $this->adminUserDepartments = $adminUserDepartments;
        return $this;
    }

    protected $adminRoleIds;

    /**
     * @return mixed
     */
    public function getAdminRoleIds()
    {
        return $this->adminRoleIds;
    }

    /**
     * @param mixed $adminRoleIds
     * @return AdminUserAdd
     */
    public function setAdminRoleIds($adminRoleIds)
    {
        $this->adminRoleIds = $adminRoleIds;
        return $this;
    }

    protected $adminMenu;

    protected $adminPageColumn;

    protected $adminPageOption;

    /**
     * @return mixed
     */
    public function getAdminMenu()
    {
        return $this->adminMenu;
    }

    /**
     * @param mixed $adminMenu
     * @return AdminUserAdd
     */
    public function setAdminMenu($adminMenu)
    {
        $this->adminMenu = $adminMenu;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAdminPageColumn()
    {
        return $this->adminPageColumn;
    }

    /**
     * @param mixed $adminPageColumn
     * @return AdminUserAdd
     */
    public function setAdminPageColumn($adminPageColumn)
    {
        $this->adminPageColumn = $adminPageColumn;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAdminPageOption()
    {
        return $this->adminPageOption;
    }

    /**
     * @param mixed $adminPageOption
     * @return AdminUserAdd
     */
    public function setAdminPageOption($adminPageOption)
    {
        $this->adminPageOption = $adminPageOption;
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
     * @return AdminUserAdd
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
     * @return AdminUserAdd
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
     * @return AdminUserAdd
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
     * @return AdminUserAdd
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
     * @return AdminUserAdd
     */
    public function setAdminRequests($adminRequests)
    {
        $this->adminRequests = $adminRequests;
        return $this;
    }
}
