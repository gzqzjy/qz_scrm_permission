<?php

namespace Qz\Admin\Permission\Cores\AdminMenu;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Qz\Admin\Permission\Cores\Core;

class GetTreeAdminMenusWithCheck extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminMenus())) {
            return;
        }
        if (empty($this->getAdminMenuIds())) {
            $this->setAdminMenuIds([]);
        }
        if (empty($this->getAdminPageColumnIds())) {
            $this->setAdminPageColumnIds([]);
        }
        if (empty($this->getAdminPageOptionIds())) {
            $this->setAdminPageOptionIds([]);
        }
        $menus = [];
        foreach ($this->getAdminMenus() as $menu) {
            $menus[] = $this->permissionItem($menu);
        }

        $this->setTreeAdminMenus($menus);
    }

    protected function permissionItem($value)
    {
        $data = [];
        Arr::set($data, 'label', Arr::get($value, 'name'));
        Arr::set($data, 'value', Arr::get($value, 'id'));
        if (in_array(Arr::get($value, 'id'), $this->getAdminMenuIds())) {
            Arr::set($data, 'check', true);
        } else {
            Arr::set($data, 'check', false);
        }
        if (Arr::get($value, 'admin_page_id')) {
            Arr::set($data, 'admin_page_id', Arr::get($value, 'admin_page_id'));
        }
        $adminPageOptions = Arr::get($value, 'admin_page.admin_page_options');
        $allCheckedOption = $allCheckedColumn = "null";
        if (!empty($adminPageOptions)) {
            Arr::set($data, 'options', array_map(function ($option) {
                return [
                    'label' => Arr::get($option, 'name'),
                    'value' => 'option_' . Arr::get($option, 'id'),
                    'check' => in_array(Arr::get($option, 'id'), $this->getAdminPageOptionIds()) ? true : false
                ];
            }, $adminPageOptions));
            $check = array_unique(Arr::pluck(Arr::get($data, 'options'), 'check'));
            if (count($check) > 1) {
                $allCheckedOption = "some";
            } else {
                $allCheckedOption = Arr::get($check, '0') ? "all" : "null";
            }
            Arr::set($data, 'optionCheck', $allCheckedOption);
        }
        $adminPageColumns = Arr::get($value, 'admin_page.admin_page_columns');
        if (!empty($adminPageColumns)) {
            Arr::set($data, 'columns', array_map(function ($column){
                return [
                    'label' => Arr::get($column, 'name'),
                    'value' => 'column_' . Arr::get($column, 'id'),
                    'check' => in_array(Arr::get($column, 'id'), $this->getAdminPageColumnIds()) ? true : false
                ];
            }, $adminPageColumns));
            $check = array_unique(Arr::pluck(Arr::get($data, 'columns'), 'check'));
            if (count($check) > 1) {
                $allCheckedColumn = "some";
            } else {
                $allCheckedColumn = Arr::get($check, '0') ? "all" : "null";
            }
            Arr::set($data, 'columnCheck', $allCheckedColumn);
        }
        if ($allCheckedOption == "all" && $allCheckedColumn == "all") {
            Arr::set($data, 'allCheck', "all");
        } elseif ($allCheckedOption == "all" || $allCheckedColumn == "all" || $allCheckedOption == "some" || $allCheckedColumn == "some") {
            Arr::set($data, 'allCheck', "some");
        } elseif (!empty($adminPageColumns) || !empty($adminPageOptions)) {
            Arr::set($data, 'allCheck', "null");
        } else {
            Arr::set($data, 'allCheck', Arr::get($data, 'check') ? "all" : "null");
        }

        if (Arr::get($value, 'children')) {
            $routes = [];
            $children = Arr::get($value, 'children');
            foreach ($children as $child) {
                $routes[] = $this->permissionItem($child);
            }
            if (!empty($routes)) {
                Arr::set($data, 'children', $routes);
                $check = array_unique(Arr::pluck($routes, 'allCheck'));
                if (count($check) > 1) {
                    Arr::set($data, 'allCheck', "some");
                } else {
                    Arr::set($data, 'allCheck', Arr::get($check, '0'));
                }
            }
        }
        return $data;
    }

    protected $adminMenus;

    protected $adminMenuIds;

    protected $adminPageColumnIds;

    protected $adminPageOptionIds;

    protected $treeAdminMenus;

    /**
     * @return mixed
     */
    public function getAdminMenus()
    {
        return $this->adminMenus;
    }

    /**
     * @param mixed $adminMenus
     * @return GetTreeAdminMenusWithCheck
     */
    public function setAdminMenus($adminMenus)
    {
        $this->adminMenus = $adminMenus;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAdminMenuIds()
    {
        return $this->adminMenuIds;
    }

    /**
     * @param mixed $adminMenuIds
     * @return GetTreeAdminMenusWithCheck
     */
    public function setAdminMenuIds($adminMenuIds)
    {
        $this->adminMenuIds = $adminMenuIds;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAdminPageColumnIds()
    {
        return $this->adminPageColumnIds;
    }

    /**
     * @param mixed $adminPageColumnIds
     * @return GetTreeAdminMenusWithCheck
     */
    public function setAdminPageColumnIds($adminPageColumnIds)
    {
        $this->adminPageColumnIds = $adminPageColumnIds;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAdminPageOptionIds()
    {
        return $this->adminPageOptionIds;
    }

    /**
     * @param mixed $adminPageOptionIds
     * @return GetTreeAdminMenusWithCheck
     */
    public function setAdminPageOptionIds($adminPageOptionIds)
    {
        $this->adminPageOptionIds = $adminPageOptionIds;
        return $this;
    }



    /**
     * @return mixed
     */
    public function getTreeAdminMenus()
    {
        return $this->treeAdminMenus;
    }

    /**
     * @param mixed $treeAdminMenus
     * @return GetTreeAdminMenusWithCheck
     */
    protected function setTreeAdminMenus($treeAdminMenus)
    {
        $this->treeAdminMenus = $treeAdminMenus;
        return $this;
    }


}
