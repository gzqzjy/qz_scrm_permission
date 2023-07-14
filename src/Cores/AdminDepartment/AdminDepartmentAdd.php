<?php

namespace Qz\Admin\Permission\Cores\AdminDepartment;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Qz\Admin\Permission\Cores\AdminCategoryDepartment\AdminCategoryDepartmentSync;
use Qz\Admin\Permission\Cores\AdminDepartmentRole\AdminDepartmentRoleSync;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminDepartment;

class AdminDepartmentAdd extends Core
{
    protected function execute()
    {
        $model = AdminDepartment::withTrashed()
            ->firstOrCreate(Arr::whereNotNull([
                'name' => $this->getName(),
                'customer_id' => $this->getCustomerId(),
            ]), Arr::whereNotNull([
                'pid' => $this->getPid(),
                'level' => $this->getLevel(),
            ]));
        if ($model->trashed()) {
            $model->restore();
        }
        $this->setId($model->getKey());
        AdminCategoryDepartmentSync::init()
            ->setCategoryIds($this->getCategoryIds())
            ->setAdminDepartmentId($this->getId())
            ->run();
        AdminDepartmentRoleSync::init()
            ->setAdminRoleIds($this->getAdminRoleIds())
            ->setAdminDepartmentId($this->getId())
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
     * @return AdminDepartmentAdd
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $param
     * @return AdminDepartmentAdd
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
     * @return AdminDepartmentAdd
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    protected $pid;

    /**
     * @return mixed
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * @param mixed $pid
     * @return AdminDepartmentAdd
     */
    public function setPid($pid)
    {
        $this->pid = $pid;
        return $this;
    }

    protected $level;

    /**
     * @return mixed
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param mixed $level
     * @return AdminDepartmentAdd
     */
    public function setLevel($level)
    {
        $this->level = $level;
        return $this;
    }

    protected $customerSubsystemId;

    /**
     * @return mixed
     */
    public function getCustomerId()
    {
        return $this->customerSubsystemId;
    }

    /**
     * @param mixed $customerSubsystemId
     * @return AdminDepartmentAdd
     */
    public function setCustomerId($customerSubsystemId)
    {
        $this->customerSubsystemId = $customerSubsystemId;
        return $this;
    }

    protected $categoryIds;

    /**
     * @return mixed
     */
    public function getCategoryIds()
    {
        return $this->categoryIds;
    }

    /**
     * @param mixed $categoryIds
     * @return AdminDepartmentAdd
     */
    public function setCategoryIds($categoryIds)
    {
        $this->categoryIds = $categoryIds;
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
     * @return AdminDepartmentAdd
     */
    public function setAdminRoleIds($adminRoleIds)
    {
        $this->adminRoleIds = $adminRoleIds;
        return $this;
    }



}
