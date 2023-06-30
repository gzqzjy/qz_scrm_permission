<?php


namespace Qz\Admin\Permission\Cores\AdminDepartment;


use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\AddShortUrlResponseBody\data;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystem;

class GetTreeCheckDepartmentWithAdminUserCustomerSubsystem extends Core
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
        $adminUserCustomerSubsystems = [];
        if ($adminUserCustomerSubsystemAndDepartments = Arr::get($this->getAdminUserCustomerSubsystemDepartments(), $id)) {
            $adminUserCustomerSubsystems = array_map(function ($adminUser) {
                return [
                    "label" => Arr::get($adminUser, 'admin_user_customer_subsystem.admin_user.name'),
                    "value" => Arr::get($adminUser, 'admin_user_customer_subsystem_id'),
                    "check" => in_array(Arr::get($adminUser, 'admin_user_customer_subsystem_id'), $this->getCheckAdminUserCustomerSubsystemIds()) ? true : false
                ];
            }, $adminUserCustomerSubsystemAndDepartments);
            $adminUserCustomerSubsystems = array_values($adminUserCustomerSubsystems);
//            $adminUserCustomerSubsystemIds = Arr::pluck($adminUserCustomerSubsystemAndDepartments, 'admin_user_customer_subsystem_id');
//            $adminUserCustomerSubsystems = Arr::only($this->getAdminUserCustomerSubsystems(), $adminUserCustomerSubsystemIds);
//            $adminUserCustomerSubsystems = array_map(function ($adminUser) {
//                return [
//                    "label" => Arr::get($adminUser, 'admin_user.name'),
//                    "value" => Arr::get($adminUser, 'id'),
//                    "check" => in_array(Arr::get($adminUser, 'id'), $this->getCheckAdminUserCustomerSubsystemIds()) ? true : false
//                ];
//            }, $adminUserCustomerSubsystems);
            Arr::set($data, 'adminUser', $adminUserCustomerSubsystems);
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
        }elseif (empty($adminUserCustomerSubsystems)){
            return [];
        }
//        if (empty($adminUserCustomerSubsystems)){
//            Log::info("log: ", $value);
//            return [];
//        }
        if ($allCheck == "some") {
            Arr::set($data, 'allCheck', "some");
        } elseif ($adminUserCustomerSubsystems) {
            $adminUserCustomerSubsystemCheck = array_unique(Arr::pluck($adminUserCustomerSubsystems, 'check'));
            if (count($adminUserCustomerSubsystemCheck) > 1) {
                Arr::set($data, 'allCheck', "some");
            } else {
                $adminUserCustomerSubsystemCheck = $adminUserCustomerSubsystemCheck[0] ? "all" : "null";
                if (empty(Arr::get($value, 'children'))) {
                    $allCheck = $adminUserCustomerSubsystemCheck;
                }else{
                    $allCheck = $adminUserCustomerSubsystemCheck != $allCheck ? "some" : $allCheck;
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

    protected $adminUserCustomerSubsystems;

    /**
     * @return mixed
     */
    public function getAdminUserCustomerSubsystems()
    {
        return $this->adminUserCustomerSubsystems;
    }

    /**
     * @param mixed $adminUserCustomerSubsystems
     * @return GetTreeDepartmentWithAdminUserCustomerSubsystem
     */
    public function setAdminUserCustomerSubsystems($adminUserCustomerSubsystems)
    {
        $this->adminUserCustomerSubsystems = $adminUserCustomerSubsystems;
        return $this;
    }

    protected $adminUserCustomerSubsystemIds;

    /**
     * @return mixed
     */
    public function getAdminUserCustomerSubsystemIds()
    {
        return $this->adminUserCustomerSubsystemIds;
    }

    /**
     * @param mixed $adminUserCustomerSubsystemIds
     * @return GetTreeDepartmentWithAdminUserCustomerSubsystem
     */
    public function setAdminUserCustomerSubsystemIds($adminUserCustomerSubsystemIds)
    {
        $this->adminUserCustomerSubsystemIds = $adminUserCustomerSubsystemIds;
        $adminUserCustomerSubsystems = AdminUserCustomerSubsystem::query()
            ->whereIn('id', $adminUserCustomerSubsystemIds)
            ->get();
        $adminUserCustomerSubsystems = $adminUserCustomerSubsystems->load([
            'adminUser:name'
        ]);
        $adminUserCustomerSubsystems = $adminUserCustomerSubsystems
            ->toArray();
        $adminUserCustomerSubsystems = array_column($adminUserCustomerSubsystems, null, 'id');
        $this->setAdminUserCustomerSubsystems($adminUserCustomerSubsystems);
        return $this;
    }


    protected $adminUserCustomerSubsystemDepartments;

    /**
     * @return mixed
     */
    public function getAdminUserCustomerSubsystemDepartments()
    {
        return $this->adminUserCustomerSubsystemDepartments;
    }

    /**
     * @param mixed $adminUserCustomerSubsystemDepartments
     * @return GetTreeDepartmentWithAdminUserCustomerSubsystem
     */
    public function setAdminUserCustomerSubsystemDepartments($adminUserCustomerSubsystemDepartments)
    {
        $this->adminUserCustomerSubsystemDepartments = $adminUserCustomerSubsystemDepartments;
        return $this;
    }

    protected $checkAdminUserCustomerSubsystemIds;

    /**
     * @return mixed
     */
    public function getCheckAdminUserCustomerSubsystemIds()
    {
        return $this->checkAdminUserCustomerSubsystemIds;
    }

    /**
     * @param mixed $checkAdminUserCustomerSubsystemIds
     * @return GetTreeCheckDepartmentWithAdminUserCustomerSubsystem
     */
    public function setCheckAdminUserCustomerSubsystemIds($checkAdminUserCustomerSubsystemIds)
    {
        $this->checkAdminUserCustomerSubsystemIds = $checkAdminUserCustomerSubsystemIds;
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
     * @return GetTreeCheckDepartmentWithAdminUserCustomerSubsystem
     */
    public function setTreeAdminDepartments($treeAdminDepartments)
    {
        $this->treeAdminDepartments = $treeAdminDepartments;
        return $this;
    }


}
