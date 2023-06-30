<?php


namespace Qz\Admin\Permission\Cores\AdminUserCustomerSubsystem;


use Dflydev\DotAccessData\Data;
use Illuminate\Support\Arr;
use Qz\Admin\Permission\Cores\AdminDepartment\GetInfoByAdminUserCustomerSubsystemId;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminRoleRequest;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystemRequestDepartment;

class GetDataPermissionByAdminUserCustomerSubsystemId extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminUserCustomerSubsystemId()) || empty($this->getDataPermission())){
            return;
        }
        $permissions = $this->getDataPermission();
        $adminUserCustomerSubsystemId = $this->getAdminUserCustomerSubsystemId();
        $adminRoleIds = GetInfoByAdminUserCustomerSubsystemId::init()
            ->setAdminUserCustomerSubsystemId($adminUserCustomerSubsystemId)
            ->getAdminUserCustomerSubsystemRoleIds();
        $adminRoleRequests = AdminRoleRequest::query()
            ->whereIn('admin_role_id', $adminRoleIds)
            ->get()
            ->groupBy('admin_request_id')
            ->toArray();

        $adminUserCustomerSubsystemRequestDepartments = [];
        $adminUserCustomerSubsystemRequestEmployees = [];

        foreach ($permissions as $permission){
            $adminRequestId = Arr::get($permission, 'admin_request_id');
            if (!Arr::exists($permission, 'admin_request_id')){
                return [];
            }
            $actions = Arr::get($permission, 'actions');
//            if (empty($actions)){
//                return [];
//            }
            if ($adminRoleRequest = Arr::get($adminRoleRequests, $adminRequestId)){
                $adminRoleRequestType = [];
                foreach ($adminRoleRequest as $item){
                    $adminRoleRequestType = array_merge($adminRoleRequestType, explode(AdminRoleRequest::CHARACTER, Arr::get($item, 'type')));
                }
                $adminRoleRequestType = array_values(array_unique($adminRoleRequestType));
                if ($adminRoleRequestType != $actions){
                    $adminUserCustomerSubsystemRequestDepartments[] = [
                        "admin_request_id" => $adminRequestId,
                        "type" => implode(AdminUserCustomerSubsystemRequestDepartment::CHARACTER, $actions)
                    ];
                }
            }else{
                $adminUserCustomerSubsystemRequestDepartments[] = [
                    "admin_request_id" => $adminRequestId,
                    "type" => implode(AdminUserCustomerSubsystemRequestDepartment::CHARACTER, $actions)
                ];
            }
            $actions = array_diff($actions, [AdminUserCustomerSubsystemRequestDepartment::UNDEFINED]);//排除其他 不返回0
            $adminUserCustomerSubsystemIds = GetAdminUserCustomerSubsystemIdsByAdminUserCustomerSubsystemIdAndType::init()
                ->setAdminUserCustomerSubSystemId($adminUserCustomerSubsystemId)
                ->setDepartmentType($actions)
                ->run()
                ->getAdminUserCustomerSubSystemIds();

            $adminUsers = Arr::get($permission, 'admin_users',[]);
            $deleteAdminUserCustomerSubsystemIds = array_diff($adminUserCustomerSubsystemIds, $adminUsers);
            $addAdminUserCustomerSubsystemIds = array_diff($adminUsers, $adminUserCustomerSubsystemIds);
            if ($deleteAdminUserCustomerSubsystemIds){
                $delete = array_map(function ($value) use ($adminRequestId){
                    return [
                        "admin_request_id" => $adminRequestId,
                        "admin_user_customer_subsystem_id" => $value,
                        "type" => "delete"
                    ];
                }, $deleteAdminUserCustomerSubsystemIds);
                $adminUserCustomerSubsystemRequestEmployees = array_merge($adminUserCustomerSubsystemRequestEmployees, $delete);
            }
            if ($addAdminUserCustomerSubsystemIds){
                $add = array_map(function ($value) use ($adminRequestId){
                    return [
                        "admin_request_id" => $adminRequestId,
                        "admin_user_customer_subsystem_id" => $value,
                        "type" => "add"
                    ];
                }, $addAdminUserCustomerSubsystemIds);
                $adminUserCustomerSubsystemRequestEmployees = array_merge($adminUserCustomerSubsystemRequestEmployees, $add);
            }
        }
        $this->setAdminUserCustomerSubsystemRequestDepartments($adminUserCustomerSubsystemRequestDepartments);
        $this->setAdminUserCustomerSubsystemRequestEmployees($adminUserCustomerSubsystemRequestEmployees);

    }

    protected $adminUserCustomerSubsystemId;

    /**
     * @return mixed
     */
    protected function getAdminUserCustomerSubsystemId()
    {
        return $this->adminUserCustomerSubsystemId;
    }

    /**
     * @param mixed $adminUserCustomerSubsystemId
     * @return GetDataPermissionByAdminUserCustomerSubsystemId
     */
    public function setAdminUserCustomerSubsystemId($adminUserCustomerSubsystemId)
    {
        $this->adminUserCustomerSubsystemId = $adminUserCustomerSubsystemId;
        return $this;
    }

    protected $dataPermission;

    /**
     * @return mixed
     */
    protected function getDataPermission()
    {
        return $this->dataPermission;
    }

    /**
     * @param mixed $dataPermission
     * @return GetDataPermissionByAdminUserCustomerSubsystemId
     */
    public function setDataPermission($dataPermission)
    {
        $this->dataPermission = $dataPermission;
        return $this;
    }

    protected $adminUserCustomerSubsystemRequestDepartments;

    /**
     * @return mixed
     */
    public function getAdminUserCustomerSubsystemRequestDepartments()
    {
        return $this->adminUserCustomerSubsystemRequestDepartments;
    }

    /**
     * @param mixed $adminUserCustomerSubsystemRequestDepartments
     * @return GetDataPermissionByAdminUserCustomerSubsystemId
     */
    protected function setAdminUserCustomerSubsystemRequestDepartments($adminUserCustomerSubsystemRequestDepartments)
    {
        $this->adminUserCustomerSubsystemRequestDepartments = $adminUserCustomerSubsystemRequestDepartments;
        return $this;
    }

    protected $adminUserCustomerSubsystemRequestEmployees;

    /**
     * @return mixed
     */
    public function getAdminUserCustomerSubsystemRequestEmployees()
    {
        return $this->adminUserCustomerSubsystemRequestEmployees;
    }

    /**
     * @param mixed $adminUserCustomerSubsystemRequestEmployees
     * @return GetDataPermissionByAdminUserCustomerSubsystemId
     */
    protected function setAdminUserCustomerSubsystemRequestEmployees($adminUserCustomerSubsystemRequestEmployees)
    {
        $this->adminUserCustomerSubsystemRequestEmployees = $adminUserCustomerSubsystemRequestEmployees;
        return $this;
    }


}
