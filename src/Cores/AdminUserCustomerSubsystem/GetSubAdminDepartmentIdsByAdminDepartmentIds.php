<?php


namespace Qz\Admin\Permission\Cores\AdminUserCustomerSubsystem;


use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminDepartment;

class GetSubAdminDepartmentIdsByAdminDepartmentIds extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminDepartmentIds())){
            return;
        }
        $subAdminDepartmentIds = $this->getAllSubAdminDepartmentIds($this->getAdminDepartmentIds());
        $this->setSubAdminDepartmentIds($subAdminDepartmentIds);
        $this->setAllAdminDepartmentIds(array_merge($this->getAdminDepartmentIds(), $subAdminDepartmentIds));
    }

    protected $adminDepartmentIds;

    protected $subAdminDepartmentIds;

    protected $allAdminDepartmentIds;

    /**
     * @return mixed
     */
    public function getAdminDepartmentIds()
    {
        return $this->adminDepartmentIds;
    }

    /**
     * @param mixed $adminDepartmentIds
     * @return GetSubAdminDepartmentIdsByAdminDepartmentIds
     */
    public function setAdminDepartmentIds($adminDepartmentIds)
    {
        $this->adminDepartmentIds = $adminDepartmentIds;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubAdminDepartmentIds()
    {
        return $this->subAdminDepartmentIds;
    }

    /**
     * @param mixed $subAdminDepartmentIds
     * @return GetSubAdminDepartmentIdsByAdminDepartmentIds
     */
    protected function setSubAdminDepartmentIds($subAdminDepartmentIds)
    {
        $this->subAdminDepartmentIds = $subAdminDepartmentIds;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAllAdminDepartmentIds()
    {
        return $this->allAdminDepartmentIds;
    }

    /**
     * @param mixed $allAdminDepartmentIds
     * @return GetSubAdminDepartmentIdsByAdminDepartmentIds
     */
    protected function setAllAdminDepartmentIds($allAdminDepartmentIds)
    {
        $this->allAdminDepartmentIds = $allAdminDepartmentIds;
        return $this;
    }



    protected function getAllSubAdminDepartmentIds($adminDepartmentIds)
    {
        $subAdminDepartmentIds = AdminDepartment::query()
            ->whereIn('pid', $adminDepartmentIds)
            ->pluck('id')
            ->toArray();
        if ($subAdminDepartmentIds){
            $itemIds = $this->getAllSubAdminDepartmentIds($subAdminDepartmentIds);
            $subAdminDepartmentIds = array_merge($subAdminDepartmentIds, $itemIds);
        }
        return $subAdminDepartmentIds;
    }

}
