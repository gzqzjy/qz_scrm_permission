<?php


namespace Qz\Admin\Permission\Cores\AdminRole;


use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminRole;


class AdminRoleUpdate extends Core
{
    protected function execute()
    {
        $model = AdminRole::withTrashed()
            ->findOrFail($this->getId());
        $model->fill(Arr::whereNotNull([
            'name' => $this->getName(),
            'admin_role_group_id' => $this->getAdminRoleGroupId(),
        ]));
        $model->save();
        $this->setId($model->getKey());
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return AdminRoleUpdate
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $param
     * @return AdminRoleUpdate
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
     * @return AdminRoleUpdate
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
     * @return AdminRoleUpdate
     */
    public function setAdminRoleGroupId($adminRoleGroupId)
    {
        $this->adminRoleGroupId = $adminRoleGroupId;
        return $this;
    }


}
