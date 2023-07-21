<?php

namespace Qz\Admin\Permission\Cores\AdminUser;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Qz\Admin\Permission\Cores\AdminDepartment\GetInfoByAdminUserId;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminRoleRequest;
use Qz\Admin\Permission\Models\AdminUser;
use Qz\Admin\Permission\Models\AdminUserRequest;
use Qz\Admin\Permission\Models\AdminUserRequestEmployee;

class GetAdminUserIdsByAdminUserId extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminUserId())) {
            return;
        }
        $adminRequestId = $this->getAdminRequestId();
        $adminRequestEmpoyee = AdminUserRequestEmployee::query()
            ->where('admin_user_id', $this->getAdminUserId())
            ->where(function (Builder $builder) use ($adminRequestId) {
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
        $adminRequestDepartment = AdminUserRequest::query()
            ->where('admin_user_id', $this->getAdminUserId())
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
            $adminRoleIds = GetInfoByAdminUserId::init()
                ->setAdminUserId($this->getAdminUserId())
                ->getAdminUserRoleIds();
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
            } else {
                $this->setAdminUserIds(null);
            }
        }

        $this->setDepartmentType($adminRequestDepartmentType);

        $adminUserIds = GetAdminUserIdsByAdminUserIdAndType::init()
            ->setAdminUserId($this->getAdminUserId())
            ->setDepartmentType($adminRequestDepartmentType)
            ->run()
            ->getAdminUserIds();

        if ($adminRequestEmpoyee && Arr::get($adminRequestEmpoyee, 'add')) {
            $adminUserIds = array_values(array_unique(array_merge($adminUserIds, Arr::pluck(Arr::get($adminRequestEmpoyee, 'add'), 'permission_admin_user_id'))));
        }

        if ($adminRequestEmpoyee && Arr::get($adminRequestEmpoyee, 'delete')) {
            $adminUserIds = array_values(array_unique(array_diff($adminUserIds, Arr::pluck(Arr::get($adminRequestEmpoyee, 'delete'), 'permission_admin_user_id'))));
        }

        $this->setAdminUserIds($adminUserIds);
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
     * @return GetAdminUserIdsByAdminUserId
     */
    public function setAdminRequestId($adminRequestId)
    {
        $this->adminRequestId = $adminRequestId;
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
     * @return GetAdminUserIdsByAdminUserId
     */
    public function setAdminUserIds($adminUserIds)
    {
        $this->adminUserIds = $adminUserIds;
        return $this;
    }

    protected $adminUserId;

    /**
     * @return mixed
     */
    public function getAdminUserId()
    {
        return $this->adminUserId;
    }

    /**
     * @param mixed $adminUserId
     * @return GetAdminUserIdsByAdminUserId
     */
    public function setAdminUserId($adminUserId)
    {
        $this->adminUserId = $adminUserId;
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
     * @return GetAdminUserIdsByAdminUserId
     */
    protected function setDepartmentType($departmentType)
    {
        $this->departmentType = $departmentType;
        return $this;
    }
}
