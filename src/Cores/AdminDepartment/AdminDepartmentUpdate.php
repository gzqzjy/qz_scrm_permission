<?php
namespace Qz\Admin\Permission\Cores\AdminDepartment;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Qz\Admin\Permission\Cores\AdminCategoryDepartment\AdminCategoryDepartmentAdd;
use Qz\Admin\Permission\Cores\AdminDepartmentRole\AdminDepartmentRoleAdd;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminCategoryDepartment;
use Qz\Admin\Permission\Models\AdminDepartment;
use Qz\Admin\Permission\Models\AdminDepartmentRole;


class AdminDepartmentUpdate extends Core
{
    protected function execute()
    {
        $model = AdminDepartment::withTrashed()
            ->findOrFail($this->getId());
        $model->fill(Arr::whereNotNull([
            'name' => $this->getName(),
            'pid' => $this->getPid(),
            'level' => $this->getLevel(),
            'customer_subsystem_id' => $this->getCustomerSubsystemId(),
        ]));
        $model->save();
        $this->setId($model->getKey());

        AdminCategoryDepartment::query()
            ->where('admin_department_id', $this->getId())
            ->delete();
        if ($this->getCategoryIds()){
            foreach ($this->getCategoryIds() as $categoryId){
                AdminCategoryDepartmentAdd::init()
                    ->setCategoryId($categoryId)
                    ->setAdminDepartmentId($this->getId())
                    ->run();
            }
        }

        AdminDepartmentRole::query()
            ->where('admin_department_id', $this->getId())
            ->delete();
        if ($this->getAdminRoleIds()){
            foreach ($this->getAdminRoleIds() as $adminRoleId){
                AdminDepartmentRoleAdd::init()
                    ->setAdminRoleId($adminRoleId)
                    ->setAdminDepartmentId($this->getId())
                    ->run();
            }
        }
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
     * @return AdminDepartmentUpdate
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $param
     * @return AdminDepartmentUpdate
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
     * @return AdminDepartmentUpdate
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
     * @return AdminDepartmentUpdate
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
     * @return AdminDepartmentUpdate
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
    public function getCustomerSubsystemId()
    {
        return $this->customerSubsystemId;
    }

    /**
     * @param mixed $customerSubsystemId
     * @return AdminDepartmentUpdate
     */
    public function setCustomerSubsystemId($customerSubsystemId)
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
     * @return AdminDepartmentUpdate
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
     * @return AdminDepartmentUpdate
     */
    public function setAdminRoleIds($adminRoleIds)
    {
        $this->adminRoleIds = $adminRoleIds;
        return $this;
    }



}
