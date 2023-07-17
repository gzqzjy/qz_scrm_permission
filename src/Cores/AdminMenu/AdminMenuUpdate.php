<?php

namespace Qz\Admin\Permission\Cores\AdminMenu;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminMenu;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AdminMenuUpdate extends Core
{
    protected function execute()
    {
        $model = AdminMenu::withTrashed()
            ->findOrFail($this->getId());
        $model->fill(Arr::whereNotNull([
            'name' => $this->getName(),
            'path' => $this->getPath(),
            'parent_id' => $this->getParentId(),
            'sort' => $this->getSort(),
            'admin_page_id' => $this->getAdminPageId(),
            'config' => $this->getConfig(),
        ]));
        $model->save();
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
     * @return AdminMenuUpdate
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param $param
     * @return AdminMenuUpdate
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
     * @return AdminMenuUpdate
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    protected $path;

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     * @return AdminMenuUpdate
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    protected $parentId;

    /**
     * @return mixed
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param mixed $parentId
     * @return AdminMenuUpdate
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
        return $this;
    }

    protected $sort;

    /**
     * @return mixed
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param mixed $sort
     * @return AdminMenuUpdate
     */
    public function setSort($sort)
    {
        $this->sort = $sort;
        return $this;
    }

    protected $adminPageId;

    /**
     * @return mixed
     */
    public function getAdminPageId()
    {
        return $this->adminPageId;
    }

    /**
     * @param mixed $adminPageId
     * @return AdminMenuUpdate
     */
    public function setAdminPageId($adminPageId)
    {
        $this->adminPageId = $adminPageId;
        return $this;
    }

    protected $config;

    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param mixed $config
     * @return AdminMenuUpdate
     */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }
}
