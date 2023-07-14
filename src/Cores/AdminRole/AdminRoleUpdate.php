<?php

namespace Qz\Admin\Permission\Cores\AdminRole;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Qz\Admin\Permission\Cores\AdminRoleMenu\AdminRoleMenuAdd;
use Qz\Admin\Permission\Cores\AdminRolePageColumn\AdminRolePageColumnAdd;
use Qz\Admin\Permission\Cores\AdminRolePageOption\AdminRolePageOptionAdd;
use Qz\Admin\Permission\Cores\AdminRoleRequest\AdminRoleRequestAdd;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminRole;
use Qz\Admin\Permission\Models\AdminRoleMenu;
use Qz\Admin\Permission\Models\AdminRolePageColumn;
use Qz\Admin\Permission\Models\AdminRolePageOption;
use Qz\Admin\Permission\Models\AdminRoleRequest;

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

        AdminRoleMenu::query()
            ->where('admin_role_id', $this->getId())
            ->delete();

        AdminRolePageColumn::query()
            ->where('admin_role_id', $this->getId())
            ->delete();

        AdminRolePageOption::query()
            ->where('admin_role_id', $this->getId())
            ->delete();

        AdminRoleRequest::query()
            ->where('admin_role_id', $this->getId())
            ->delete();

        if ($this->getAdminRoleMenu()) {
            foreach ($this->getAdminRoleMenu() as $adminMenuId) {
                AdminRoleMenuAdd::init()
                    ->setAdminRoleId($this->getId())
                    ->setAdminMenuId($adminMenuId)
                    ->run();
            }
        }

        if ($this->getAdminRolePageColumn()) {
            foreach ($this->getAdminRolePageColumn() as $adminPageColumnId) {
                AdminRolePageColumnAdd::init()
                    ->setAdminRoleId($this->getId())
                    ->setAdminPageColumnId($adminPageColumnId)
                    ->run();
            }
        }

        if ($this->getAdminRolePageOption()) {
            foreach ($this->getAdminRolePageOption() as $adminPageOptionId) {
                AdminRolePageOptionAdd::init()
                    ->setAdminRoleId($this->getId())
                    ->setAdminPageOptionId($adminPageOptionId)
                    ->run();
            }
        }

        if ($this->getAdminRoleRequest()) {
            foreach ($this->getAdminRoleRequest() as $item) {
                AdminRoleRequestAdd::init()
                    ->setAdminRoleId($this->getId())
                    ->setAdminRequestId(Arr::get($item, 'admin_request_id'))
                    ->setType(Arr::get($item, 'type'))
                    ->run();
            }
        }
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

    protected $adminRoleMenu;

    /**
     * @return mixed
     */
    public function getAdminRoleMenu()
    {
        return $this->adminRoleMenu;
    }

    /**
     * @param mixed $adminRoleMenu
     * @return AdminRoleUpdate
     */
    public function setAdminRoleMenu($adminRoleMenu)
    {
        $this->adminRoleMenu = $adminRoleMenu;
        return $this;
    }

    protected $adminRolePageColumn;

    /**
     * @return mixed
     */
    public function getAdminRolePageColumn()
    {
        return $this->adminRolePageColumn;
    }

    /**
     * @param mixed $adminRolePageColumn
     * @return AdminRoleUpdate
     */
    public function setAdminRolePageColumn($adminRolePageColumn)
    {
        $this->adminRolePageColumn = $adminRolePageColumn;
        return $this;
    }

    protected $adminRolePageOption;

    /**
     * @return mixed
     */
    public function getAdminRolePageOption()
    {
        return $this->adminRolePageOption;
    }

    /**
     * @param mixed $adminRolePageOption
     * @return AdminRoleUpdate
     */
    public function setAdminRolePageOption($adminRolePageOption)
    {
        $this->adminRolePageOption = $adminRolePageOption;
        return $this;
    }

    protected $adminRoleRequest;

    /**
     * @return mixed
     */
    public function getAdminRoleRequest()
    {
        return $this->adminRoleRequest;
    }

    /**
     * @param mixed $adminRoleRequest
     * @return AdminRoleUpdate
     */
    public function setAdminRoleRequest($adminRoleRequest)
    {
        $this->adminRoleRequest = $adminRoleRequest;
        return $this;
    }
}
