<?php

namespace Qz\Admin\Permission\Cores\AdminUser;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminDepartment;
use Qz\Admin\Permission\Models\AdminRoleRequest;
use Qz\Admin\Permission\Models\AdminUser;
use Qz\Admin\Permission\Models\AdminUserDepartment;
use Qz\Admin\Permission\Models\AdminUserRequest;

class AdminUserIdsByAdminUserIdGet extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminUserId())) {
            return;
        }
        $adminUser = AdminUser::query()
            ->select(['id', 'customer_id'])
            ->find($this->getAdminUserId());
        if (empty($adminUser)) {
            return;
        }
        $adminUser->load('administrator');
        if (Arr::get($adminUser, 'administrator.id')) {
            $ids = AdminUser::query()
                ->where('customer_id', Arr::get($adminUser, 'customer_id'))
                ->pluck('id')
                ->toArray();
            $this->ids[] = 0;
            if (!empty($ids)) {
                $this->ids = array_unique(array_merge($this->ids, $ids));
            }
            return;
        }
        $adminUserRequest = AdminUserRequest::query()
            ->select(['type'])
            ->where('admin_user_id', $this->getAdminUserId())
            ->where('admin_request_id', $this->getAdminRequestId())
            ->first();
        if ($adminUserRequest) {
            $this->getIdsByTypes(Arr::get($adminUserRequest, 'types'));
            return;
        }
        $adminRoleRequests = AdminRoleRequest::query()
            ->select(['type'])
            ->where('admin_request_id', $this->getAdminRequestId())
            ->whereHas('adminRole', function (Builder $builder) {
                $builder->whereHas('adminUserRoles', function (Builder $builder) {
                    $builder->where('admin_user_id', $this->getAdminUserId());
                });
            })->get();
        if (!empty($adminRoleRequests) && count($adminRoleRequests)) {
            $types = [];
            foreach ($adminRoleRequests as $adminRoleRequest) {
                $types = array_merge($types, Arr::get($adminRoleRequest, 'types'));
            }
            $this->getIdsByTypes($types);
            return;
        }
        $adminUserRequest = AdminUserRequest::query()
            ->select(['type'])
            ->where('admin_user_id', $this->getAdminUserId())
            ->where('admin_request_id', 0)
            ->first();
        if ($adminUserRequest) {
            $this->getIdsByTypes(Arr::get($adminUserRequest, 'types'));
            return;
        }
        $adminUserRequest = AdminUserRequest::query()
            ->select(['type'])
            ->where('admin_user_id', 0)
            ->where('admin_request_id', 0)
            ->first();
        if ($adminUserRequest) {
            $this->getIdsByTypes(Arr::get($adminUserRequest, 'types'));
            return;
        }
        $adminUserRequest = AdminRoleRequest::query()
            ->select(['type'])
            ->where('admin_role_id', 0)
            ->where('admin_request_id', 0)
            ->first();
        if ($adminUserRequest) {
            $this->getIdsByTypes(Arr::get($adminUserRequest, 'types'));
            return;
        }
    }

    protected function getIdsByTypes($types = [])
    {
        $types = array_unique($types);
        foreach ($types as $type) {
            if ($type == AdminUserRequest::SELF) {
                $this->ids[] = $this->getAdminUserId();
            } elseif ($type == AdminUserRequest::UNDEFINED) {
                $this->ids[] = 0;
            } elseif ($type == AdminUserRequest::THIS) {
                $adminDepartmentIds = AdminDepartmentIdsByAdminUserIdGet::init()
                    ->setAdminUserId($this->getAdminUserId())
                    ->run()
                    ->getIds();
                $ids = AdminUserDepartment::query()
                    ->where('admin_user_id', '!=', $this->getAdminUserId())
                    ->whereIn('admin_department_id', $adminDepartmentIds)
                    ->pluck('admin_user_id')
                    ->toArray();
                $this->ids = array_unique(array_merge($this->ids, $ids));
            } elseif ($type == AdminUserRequest::PEER) {
                $adminDepartmentIds = AdminDepartmentIdsByAdminUserIdGet::init()
                    ->setAdminUserId($this->getAdminUserId())
                    ->run()
                    ->getIds();
                $ids = AdminUserDepartment::query()
                    ->where('admin_user_id', '!=', $this->getAdminUserId())
                    ->whereHas('adminDepartment', function (Builder $builder) use ($adminDepartmentIds) {
                        $builder->whereIn('pid', $adminDepartmentIds);
                    })
                    ->pluck('admin_user_id')
                    ->toArray();
                $this->ids = array_unique(array_merge($this->ids, $ids));
            } elseif ($type == AdminUserRequest::CHILDREN) {
                $adminDepartmentIds = AdminDepartmentIdsByAdminUserIdGet::init()
                    ->setAdminUserId($this->getAdminUserId())
                    ->run()
                    ->getIds();
                $adminDepartments = AdminDepartment::query()
                    ->select(['id', 'pid'])
                    ->with('children')
                    ->whereIn('pid', $adminDepartmentIds)
                    ->get();
                $adminDepartmentIds = [];
                $adminDepartmentIds = $this->getAllAdminDepartmentIds($adminDepartments, $adminDepartmentIds);
                $ids = AdminUserDepartment::query()
                    ->whereIn('admin_department_id', $adminDepartmentIds)
                    ->where('admin_user_id', '!=', $this->getAdminUserId())
                    ->pluck('admin_user_id')
                    ->toArray();
                $this->ids = array_unique(array_merge($this->ids, $ids));
            }
        }
    }

    protected function getAllAdminDepartmentIds($items, $ids = [])
    {
        if (empty($items)) {
            return $ids;
        }
        foreach ($items as $item) {
            $ids[] = Arr::get($item, 'id');
            $children = Arr::get($item, 'children');
            if ($children && count($children)) {
                $ids = $this->getAllAdminDepartmentIds($children, $ids);
            }
        }
        return $ids;
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
     * @return $this
     */
    public function setAdminUserId($adminUserId)
    {
        $this->adminUserId = $adminUserId;
        return $this;
    }

    protected $ids = [];

    /**
     * @return array
     */
    public function getIds()
    {
        return $this->ids;
    }

    /**
     * @param array $ids
     * @return $this
     */
    public function setIds($ids)
    {
        $this->ids = $ids;
        return $this;
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
     * @return AdminUserIdsByAdminUserIdGet
     */
    public function setAdminRequestId($adminRequestId)
    {
        $this->adminRequestId = $adminRequestId;
        return $this;
    }

    protected $types = [];

    /**
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @param array $types
     * @return AdminUserIdsByAdminUserIdGet
     */
    public function setTypes($types)
    {
        $this->types = $types;
        return $this;
    }
}
