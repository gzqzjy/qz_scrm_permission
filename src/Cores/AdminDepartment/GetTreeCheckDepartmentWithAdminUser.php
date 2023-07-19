<?php

namespace Qz\Admin\Permission\Cores\AdminDepartment;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUser;

class GetTreeCheckDepartmentWithAdminUser extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminDepartments())) {
            return;
        }
        $data = [];
        foreach ($this->getAdminDepartments() as $value) {
            $data[] = $this->item($value);
        }
        $this->setTreeAdminDepartments($data);
    }

    protected function item($value)
    {
        $id = Arr::get($value, "value");
        $data = [
            "label" => Arr::get($value, 'label'),
            "value" => 'department_' . $id
        ];
        $adminUsers = [];
        if ($adminUserAndDepartments = Arr::get($this->getAdminUserDepartments(), $id)) {
            $adminUsers = array_map(function ($adminUser) {
                return [
                    "label" => Arr::get($adminUser, 'admin_user.name'),
                    "value" => Arr::get($adminUser, 'admin_user_id'),
                    "check" => in_array(Arr::get($adminUser, 'admin_user_id'), $this->getCheckAdminUserIds()) ? true : false
                ];
            }, $adminUserAndDepartments);
            $adminUsers = array_values($adminUsers);
//            $adminUserIds = Arr::pluck($adminUserAndDepartments, 'admin_user_id');
//            $adminUsers = Arr::only($this->getAdminUsers(), $adminUserIds);
//            $adminUsers = array_map(function ($adminUser) {
//                return [
//                    "label" => Arr::get($adminUser, 'admin_user.name'),
//                    "value" => Arr::get($adminUser, 'id'),
//                    "check" => in_array(Arr::get($adminUser, 'id'), $this->getCheckAdminUserIds()) ? true : false
//                ];
//            }, $adminUsers);
            Arr::set($data, 'adminUser', $adminUsers);
        }
        $allCheck = "null";
        if (Arr::get($value, 'children')) {
            $routes = [];
            $children = Arr::get($value, 'children');
            foreach ($children as $child) {
                if ($item = $this->item($child)) {
                    $routes[] = $item;
                }
            }
            if ($routes) {
                Arr::set($data, 'children', $routes);
                $allCheck = array_unique(Arr::pluck($routes, 'allCheck'));
                if (count($allCheck) > 1) {
                    $allCheck = "some";
                } else {
                    $allCheck = $allCheck[0];
                }
            }
        }elseif (empty($adminUsers)){
            return [];
        }
//        if (empty($adminUsers)){
//            Log::info("log: ", $value);
//            return [];
//        }
        if ($allCheck == "some") {
            Arr::set($data, 'allCheck', "some");
        } elseif ($adminUsers) {
            $adminUserCheck = array_unique(Arr::pluck($adminUsers, 'check'));
            if (count($adminUserCheck) > 1) {
                Arr::set($data, 'allCheck', "some");
            } else {
                $adminUserCheck = $adminUserCheck[0] ? "all" : "null";
                if (empty(Arr::get($value, 'children'))) {
                    $allCheck = $adminUserCheck;
                }else{
                    $allCheck = $adminUserCheck != $allCheck ? "some" : $allCheck;
                }
                Arr::set($data, 'allCheck', $allCheck);
            }
        } else {
            Arr::set($data, 'allCheck', $allCheck);
        }
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

    protected $adminUsers;

    /**
     * @return mixed
     */
    public function getAdminUsers()
    {
        return $this->adminUsers;
    }

    /**
     * @param mixed $adminUsers
     * @return GetTreeDepartmentWithAdminUser
     */
    public function setAdminUsers($adminUsers)
    {
        $this->adminUsers = $adminUsers;
        return $this;
    }

    protected $adminUserIds;

    /**
     * @return mixed
     */
    public function getAdminUserIds()
    {
        return $this->adminUserIds;
    }

    /**
     * @param mixed $adminUserIds
     * @return GetTreeDepartmentWithAdminUser
     */
    public function setAdminUserIds($adminUserIds)
    {
        $this->adminUserIds = $adminUserIds;
        $adminUsers = AdminUser::query()
            ->whereIn('id', $adminUserIds)
            ->get();
        $adminUsers = $adminUsers
            ->toArray();
        $adminUsers = array_column($adminUsers, null, 'id');
        $this->setAdminUsers($adminUsers);
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
     * @return GetTreeDepartmentWithAdminUser
     */
    public function setAdminUserDepartments($adminUserDepartments)
    {
        $this->adminUserDepartments = $adminUserDepartments;
        return $this;
    }

    protected $checkAdminUserIds;

    /**
     * @return mixed
     */
    public function getCheckAdminUserIds()
    {
        return $this->checkAdminUserIds;
    }

    /**
     * @param mixed $checkAdminUserIds
     * @return GetTreeCheckDepartmentWithAdminUser
     */
    public function setCheckAdminUserIds($checkAdminUserIds)
    {
        $this->checkAdminUserIds = $checkAdminUserIds;
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
     * @return GetTreeCheckDepartmentWithAdminUser
     */
    public function setTreeAdminDepartments($treeAdminDepartments)
    {
        $this->treeAdminDepartments = $treeAdminDepartments;
        return $this;
    }


}
