<?php

namespace Qz\Admin\Permission\Cores\AdminUser;

use Illuminate\Support\Arr;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUser;

class AdminRequestsByAdminUserIdGet extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminUserId())) {
            return;
        }
        $model = AdminUser::query()
            ->select(['id'])
            ->find($this->getAdminUserId());
        if (empty($model)) {
            return;
        }
        $model->load([
            'adminUserRoles',
            'adminUserRoles.adminRole',
            'adminUserRoles.adminRole.adminRoleRequests',
            'adminUserRequests',
        ]);
        $adminUserRoles = Arr::get($model, 'adminUserRoles');
        foreach ($adminUserRoles as $adminUserRole) {
            $adminRole = Arr::get($adminUserRole, 'adminRole');
            if (empty($adminRole)) {
                continue;
            }
            $adminRoleRequests = Arr::get($adminRole, 'adminRoleRequests');
            foreach ($adminRoleRequests as $adminRoleRequest) {
                $this->add(Arr::get($adminRoleRequest, 'admin_request_id'), Arr::get($adminRoleRequest, 'types'));
            }
        }
        $adminUserRequests = Arr::get($model, 'adminUserRequests');
        foreach ($adminUserRequests as $adminUserRequest) {
            $adminRequestId = Arr::get($adminRoleRequest, 'admin_request_id');
            $this->adminRequests[$adminRequestId] = [
                'admin_request_id' => $adminRequestId,
                'types' => Arr::get($adminUserRequest, 'types'),
            ];
        }
        $this->adminRequests = array_values($this->adminRequests);
    }

    protected function add($adminRequestId, $types)
    {
        if (!Arr::has($this->adminRequests, $adminRequestId)) {
            $this->adminRequests[$adminRequestId] = [
                'admin_request_id' => $adminRequestId,
                'types' => $types,
            ];
        } else if (is_array($types)) {
            $this->adminRequests[$adminRequestId]['type'] = array_unique(array_merge($types, $this->adminRequests[$adminRequestId]['type']));
        } else {
            $this->adminRequests[$adminRequestId]['type'] = array_unique(Arr::prepend($types, $this->adminRequests[$adminRequestId]['type']));
        }
    }

    protected $adminRequests = [];

    /**
     * @return mixed
     */
    public function getAdminRequests()
    {
        return $this->adminRequests;
    }

    /**
     * @param mixed $adminRequests
     * @return $this
     */
    public function setAdminRequests($adminRequests)
    {
        $this->adminRequests = $adminRequests;
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
     * @return AdminRequestsByAdminUserIdGet
     */
    public function setAdminUserId($adminUserId)
    {
        $this->adminUserId = $adminUserId;
        return $this;
    }
}
