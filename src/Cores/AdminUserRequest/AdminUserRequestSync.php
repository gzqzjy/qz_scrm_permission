<?php

namespace Qz\Admin\Permission\Cores\AdminUserRequest;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserRequest;
use Illuminate\Support\Arr;

class AdminUserRequestSync extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminUserId())) {
            return;
        }
        $adminRequests = $this->getAdminRequests();
        if (is_null($adminRequests)) {
            return;
        }
        AdminUserRequest::query()
            ->where('admin_user_id', $this->getAdminUserId())
            ->whereNotIn('admin_request_id', Arr::pluck($adminRequests, $this->getAdminRequestIdKey()))
            ->delete();
        if (!empty($adminRequests)) {
            foreach ($adminRequests as $adminRequest) {
                AdminUserRequestAdd::init()
                    ->setAdminRequestId(Arr::get($adminRequest, $this->getAdminRequestIdKey()))
                    ->setType(implode(AdminUserRequest::CHARACTER, Arr::get($adminRequest, 'types')))
                    ->setAdminUserId($this->getAdminUserId())
                    ->run();
            }
        }
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
     * @param $adminUserId
     * @return $this
     */
    public function setAdminUserId($adminUserId)
    {
        $this->adminUserId = $adminUserId;
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
     * @return AdminUserRequestSync
     */
    public function setAdminRequests($adminRequests)
    {
        $this->adminRequests = $adminRequests;
        return $this;
    }

    protected $adminRequestIdKey = 'admin_request_id';

    /**
     * @return string
     */
    public function getAdminRequestIdKey()
    {
        return $this->adminRequestIdKey;
    }

    /**
     * @param string $adminRequestIdKey
     * @return AdminUserRequestSync
     */
    public function setAdminRequestIdKey($adminRequestIdKey)
    {
        $this->adminRequestIdKey = $adminRequestIdKey;
        return $this;
    }
}
