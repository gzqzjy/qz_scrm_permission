<?php
namespace Qz\Admin\Permission\Cores\AdminRoleRequest;

use Illuminate\Support\Arr;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminRoleRequest;

class AdminRoleRequestSync extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminRoleId())) {
            return;
        }
        if (is_null($this->getAdminRequests())) {
            return;
        }
        AdminRoleRequest::query()
            ->select(['id'])
            ->where('admin_role_id', $this->getAdminRoleId())
            ->delete();
        $adminRequests = $this->getAdminRequests();
        if (!empty($adminRequests)) {
            foreach ($adminRequests as $adminRequest) {
                AdminRoleRequestAdd::init()
                    ->setAdminRoleId($this->getAdminRoleId())
                    ->setType(implode(AdminRoleRequest::CHARACTER, Arr::get($adminRequest, 'types')))
                    ->setAdminRequestId(Arr::get($adminRequest, 'admin_request_id'))
                    ->run();
            }
        }
    }

    protected $adminRoleId;

    /**
     * @return mixed
     */
    public function getAdminRoleId()
    {
        return $this->adminRoleId;
    }

    /**
     * @param mixed $adminRoleId
     * @return AdminRoleRequestSync
     */
    public function setAdminRoleId($adminRoleId)
    {
        $this->adminRoleId = $adminRoleId;
        return $this;
    }

    protected $adminRequests;

    /**
     * @return mixed
     */
    public function getAdminRequests()
    {
        return $this->adminRequests;
    }

    /**
     * @param mixed $adminRequests
     * @return AdminRoleRequestSync
     */
    public function setAdminRequests($adminRequests)
    {
        $this->adminRequests = $adminRequests;
        return $this;
    }
}
