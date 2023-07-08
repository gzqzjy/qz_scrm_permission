<?php

namespace Qz\Admin\Permission\Cores\AdminUser;

use Qz\Admin\Permission\Cores\AdminUserCustomerSubsystem\AdminUserCustomerSubsystemAdd;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUser;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystem;

class AdminUserAdd extends Core
{
    protected function execute()
    {
        $model = AdminUser::withTrashed()
            ->updateOrCreate(Arr::whereNotNull([
                'mobile' => $this->getMobile(),
            ]), Arr::whereNotNull([
                'name' => $this->getName(),
                'status' => $this->getStatus(),
                'sex' => $this->getSex(),
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

    protected $customerSubsystemId;

    /**
     * @return mixed
     */
    public function getCustomerSubsystemId()
    {
        return $this->customerSubsystemId;
    }

    /**
     * @param mixed $customerSubsystemId
     * @return AdminUserAdd
     */
    public function setCustomerSubsystemId($customerSubsystemId)
    {
        $this->customerSubsystemId = $customerSubsystemId;
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

    protected $adminDepartments;

    /**
     * @return mixed
     */
    public function getAdminDepartments()
    {
        return $this->adminDepartments;
    }

    /**
     * @param mixed $adminDepartments
     * @return AdminUserAdd
     */
    public function setAdminDepartments($adminDepartments)
    {
        $this->adminDepartments = $adminDepartments;
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




}
