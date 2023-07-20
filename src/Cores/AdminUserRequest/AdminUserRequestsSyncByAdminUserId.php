<?php

namespace Qz\Admin\Permission\Cores\AdminUserRequest;

use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminUserRequest;
use Illuminate\Support\Arr;

class AdminUserRequestsSyncByAdminUserId extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminUserId())) {
            return;
        }
        $adminUserRequests = $this->getAdminUserRequests();
        if (is_null($adminUserRequests)) {
            return;
        }
        $deletes = AdminUserRequest::query()
            ->select('id')
            ->where('admin_user_id', $this->getAdminUserId())
            ->whereNotIn('admin_request_id', Arr::pluck($adminUserRequests, $this->getAdminRequestIdKey()))
            ->get();
        foreach ($deletes as $delete) {
            AdminUserRequestDelete::init()
                ->setId(Arr::get($delete, 'id'))
                ->run();
        }
        if (!empty($adminUserRequests)) {
            foreach ($adminUserRequests as $adminUserRequest) {
                AdminUserRequestAdd::init()
                    ->setAdminRequestId(Arr::get($adminUserRequest, $this->getAdminRequestIdKey()))
                    ->setType(Arr::get($adminUserRequest, 'type'))
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

    protected $adminUserRequests;

    /**
     * @return mixed
     */
    public function getAdminUserRequests()
    {
        return $this->adminUserRequests;
    }

    /**
     * @param mixed $adminUserRequests
     * @return AdminUserRequestsSyncByAdminUserId
     */
    public function setAdminUserRequests($adminUserRequests)
    {
        $this->adminUserRequests = $adminUserRequests;
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
     * @return AdminUserRequestsSyncByAdminUserId
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
     * @return AdminUserRequestsSyncByAdminUserId
     */
    public function setAdminRequestIdKey($adminRequestIdKey)
    {
        $this->adminRequestIdKey = $adminRequestIdKey;
        return $this;
    }
}
