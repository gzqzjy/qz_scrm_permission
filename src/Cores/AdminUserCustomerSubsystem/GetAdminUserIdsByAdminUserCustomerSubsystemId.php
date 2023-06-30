<?php


namespace Qz\Admin\Permission\Cores\AdminUserCustomerSubsystem;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Qz\Admin\Permission\Cores\AdminDepartment\GetInfoByAdminUserCustomerSubsystemId;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminRoleRequest;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystem;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystemRequestDepartment;
use Qz\Admin\Permission\Models\AdminUserCustomerSubsystemRequestEmployee;

class GetAdminUserIdsByAdminUserCustomerSubsystemId extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminUserCustomerSubSystemId())) {
            return;
        }
        $adminRequestId = $this->getAdminRequestId();
        $adminRequestEmpoyee = AdminUserCustomerSubsystemRequestEmployee::query()
            ->where('admin_user_customer_subsystem_id', $this->getAdminUserCustomerSubSystemId())
            ->where(function (Builder $builder) use ($adminRequestId){
                if ($adminRequestId) {
                    $builder->where('admin_request_id', $adminRequestId)
                        ->orWhere('admin_request_id', 0);
                } else {
                    $builder->where('admin_request_id', 0);
                }
            })
            ->orderByDesc('admin_request_id')
            ->get();
        if ($adminRequestEmpoyee->isNotEmpty()) {
            $adminRequestEmpoyee = $adminRequestEmpoyee->groupBy(['admin_request_id', 'type'])->toArray();
            if (Arr::get($adminRequestEmpoyee, $adminRequestId)) {
                $adminRequestEmpoyee = Arr::get($adminRequestEmpoyee, $adminRequestId);
            } else {
                $adminRequestEmpoyee = Arr::get($adminRequestEmpoyee, 0);
            }
        }
        $adminRequestDepartment = AdminUserCustomerSubsystemRequestDepartment::query()
            ->where('admin_user_customer_subsystem_id', $this->getAdminUserCustomerSubSystemId())
            ->where(function (Builder $builder) {
                if ($adminRequestId = $this->getAdminRequestId()) {
                    $builder->where('admin_request_id', $adminRequestId)
                        ->orWhere('admin_request_id', 0);
                } else {
                    $builder->where('admin_request_id', 0);
                }
            })
            ->orderByDesc('admin_request_id')
            ->select(['admin_request_id', 'type'])
            ->first();

        $adminRequestDepartmentType = Arr::get($adminRequestDepartment, 'type');
        $adminRequestDepartmentId = Arr::get($adminRequestDepartment, 'admin_request_id');
        if (empty($adminRequestDepartment)) {
            $adminRoleIds = GetInfoByAdminUserCustomerSubsystemId::init()
                ->setAdminUserCustomerSubsystemId($this->getAdminUserCustomerSubSystemId())
                ->getAdminUserCustomerSubsystemRoleIds();
            if ($adminRoleIds) {
                $adminRequestDepartment = AdminRoleRequest::query()
                    ->whereIn('admin_role_id', $adminRoleIds)
                    ->where(function (Builder $builder) {
                        if ($adminRequestId = $this->getAdminRequestId()) {
                            $builder->where('admin_request_id', $adminRequestId)
                                ->orWhere('admin_request_id', 0);
                        } else {
                            $builder->where('admin_request_id', 0);
                        }
                    })
                    ->orderByDesc('admin_request_id')
                    ->get();

                if ($adminRequestDepartment->isNotEmpty()) {
                    $adminRequestDepartment = $adminRequestDepartment
                        ->groupBy('admin_role_id')
                        ->toArray();
                    $adminRequestDepartmentType = [];
                    foreach ($adminRequestDepartment as $adminRoleRequest) {
                        $type = Arr::get($adminRoleRequest, '0.type');
                        $adminRequestDepartmentType = array_merge($adminRequestDepartmentType, explode(AdminRoleRequest::CHARACTER, $type));
                    }
                    $adminRequestDepartmentType = array_values(array_unique($adminRequestDepartmentType));
                }
            }else{
                $this->setAdminUserCustomerSubSystemIds(null);
            }
        }

        $this->setDepartmentType($adminRequestDepartmentType);

        $adminUserCustomerSubSystemIds = GetAdminUserCustomerSubsystemIdsByAdminUserCustomerSubsystemIdAndType::init()
            ->setAdminUserCustomerSubSystemId($this->getAdminUserCustomerSubSystemId())
            ->setDepartmentType($adminRequestDepartmentType)
            ->run()
            ->getAdminUserCustomerSubSystemIds();

        if ($adminRequestEmpoyee && Arr::get($adminRequestEmpoyee, 'add')){
            $adminUserCustomerSubSystemIds = array_values(array_unique(array_merge($adminUserCustomerSubSystemIds, Arr::pluck(Arr::get($adminRequestEmpoyee, 'add'), 'permission_admin_user_customer_subsystem_id'))));
        }

        if ($adminRequestEmpoyee && Arr::get($adminRequestEmpoyee, 'delete')){
            $adminUserCustomerSubSystemIds = array_values(array_unique(array_diff($adminUserCustomerSubSystemIds, Arr::pluck(Arr::get($adminRequestEmpoyee, 'delete'), 'permission_admin_user_customer_subsystem_id'))));
        }

        $this->setAdminUserCustomerSubSystemIds($adminUserCustomerSubSystemIds);

    }

    protected $adminRequestId;

    /**
     * @return mixed
     */
    public function getAdminRequestId()
    {
        return $this->adminRequestId;
    }

    /**
     * @param mixed $adminRequestId
     * @return GetAdminUserIdsByAdminUserCustomerSubsystemId
     */
    public function setAdminRequestId($adminRequestId)
    {
        $this->adminRequestId = $adminRequestId;
        return $this;
    }


    protected $adminUserCustomerSubSystemIds;

    /**
     * @return mixed
     */
    public function getAdminUserCustomerSubSystemIds()
    {
        return $this->adminUserCustomerSubSystemIds;
    }

    /**
     * @param mixed $adminUserCustomerSubSystemIds
     * @return GetAdminUserIdsByAdminUserCustomerSubsystemId
     */
    public function setAdminUserCustomerSubSystemIds($adminUserCustomerSubSystemIds)
    {
        $this->adminUserCustomerSubSystemIds = $adminUserCustomerSubSystemIds;
        return $this;
    }

    protected $adminUserCustomerSubSystemId;

    /**
     * @return mixed
     */
    public function getAdminUserCustomerSubSystemId()
    {
        return $this->adminUserCustomerSubSystemId;
    }

    /**
     * @param mixed $adminUserCustomerSubSystemId
     * @return GetAdminUserIdsByAdminUserCustomerSubsystemId
     */
    public function setAdminUserCustomerSubSystemId($adminUserCustomerSubSystemId)
    {
        $this->adminUserCustomerSubSystemId = $adminUserCustomerSubSystemId;
        return $this;
    }


    protected $adminUserIds;

    /**
     * @return mixed
     */
    public function getAdminUserIds()
    {
        if (!is_null($this->getAdminUserCustomerSubSystemIds())) {
            $adminUserIds = AdminUserCustomerSubsystem::query()
                ->whereIn('id', $this->getAdminUserCustomerSubSystemIds())
                ->pluck('admin_user_id')
                ->toArray();

            is_array($this->getDepartmentType()) && in_array(AdminUserCustomerSubsystemRequestDepartment::UNDEFINED, $this->getDepartmentType()) && $adminUserIds[] = 0;
            !is_array($this->getDepartmentType()) && strpos($this->getDepartmentType(), AdminUserCustomerSubsystemRequestDepartment::UNDEFINED) !== false && $adminUserIds[] = 0;
            return $adminUserIds;
        }
        return $this->adminUserIds;
    }

    /**
     * @param mixed $adminUserIds
     * @return GetAdminUserIdsByAdminUserCustomerSubsystemId
     */
    public function setAdminUserIds($adminUserIds)
    {
        $this->adminUserIds = $adminUserIds;
        return $this;
    }


    protected $departmentType;

    /**
     * @return mixed
     */
    public function getDepartmentType()
    {
        return $this->departmentType;
    }

    /**
     * @param mixed $departmentType
     * @return GetAdminUserIdsByAdminUserCustomerSubsystemId
     */
    protected function setDepartmentType($departmentType)
    {
        $this->departmentType = $departmentType;
        return $this;
    }


}
