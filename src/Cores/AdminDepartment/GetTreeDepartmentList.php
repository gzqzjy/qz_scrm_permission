<?php


namespace Qz\Admin\Permission\Cores\AdminDepartment;


use Illuminate\Support\Arr;
use Qz\Admin\Permission\Cores\Core;

class GetTreeDepartmentList extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminDepartments())){
            return;
        }
        $data = [];
        $existDepartmentIds = [];
        foreach ($this->getAdminDepartments() as $value) {
            if ($item = $this->item($value,$this->getAdminDepartments(), $existDepartmentIds, Arr::get($value, 'id'))){
                $data[] = $item;
            }
        }
        $this->setTreeAdminDepartments($data);
    }

    protected function item($value,$array, &$existDepartmentIds, $pid = 0)
    {
        if (in_array(Arr::get($value, 'id'), $existDepartmentIds)){
            return [];
        }
        $existDepartmentIds[] = Arr::get($value, 'id');
        $data = [
            "label" => Arr::get($value, 'name'),
            "value" => Arr::get($value, "id")
        ];
        $children = [];
        foreach ($array as $item) {
            if (Arr::get($item, 'pid') == $pid) {
                if ($child = $this->item($item, $array, $existDepartmentIds, Arr::get($item, 'id'))) {
                    $children[] = $child;
                }
            }
        }
        $data['children'] = $children;
        return $data;

    }

    protected $adminDepartments;

    /**
     * @return mixed
     */
    protected function getAdminDepartments()
    {
        return $this->adminDepartments;
    }

    /**
     * @param mixed $adminDepartments
     * @return GetTreeDepartmentList
     */
    public function setAdminDepartments($adminDepartments)
    {
        $this->adminDepartments = $adminDepartments;
        return $this;
    }

    protected $treeAdminDepartments;

    /**
     * @return mixed
     */
    public function getTreeAdminDepartments()
    {
        return $this->treeAdminDepartments;
    }

    /**
     * @param mixed $treeAdminDepartments
     * @return GetTreeDepartmentList
     */
    protected function setTreeAdminDepartments($treeAdminDepartments)
    {
        $this->treeAdminDepartments = $treeAdminDepartments;
        return $this;
    }

}
