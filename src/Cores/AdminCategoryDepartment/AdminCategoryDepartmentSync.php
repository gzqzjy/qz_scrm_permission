<?php
namespace Qz\Admin\Permission\Cores\AdminCategoryDepartment;

use Illuminate\Support\Arr;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminCategoryDepartment;

class AdminCategoryDepartmentSync extends Core
{
    protected function execute()
    {
        if (empty($this->getAdminDepartmentId())) {
            return;
        }
        if (is_null($this->getCategoryIds())) {
            return;
        }
        $adminCategoryDepartments = AdminCategoryDepartment::query()
            ->select(['id'])
            ->where('admin_department_id', $this->getAdminDepartmentId())
            ->get();
        foreach ($adminCategoryDepartments as $adminCategoryDepartment) {
            AdminCategoryDepartmentDelete::init()
                ->setId(Arr::get($adminCategoryDepartment, 'id'))
                ->run();
        }
        $categoryIds = $this->getCategoryIds();
        if (!empty($categoryIds)) {
            foreach ($categoryIds as $categoryId) {
                AdminCategoryDepartmentAdd::init()
                    ->setAdminDepartmentId($this->getAdminDepartmentId())
                    ->setCategoryId($categoryId)
                    ->run();
            }
        }
    }

    protected $adminDepartmentId;

    /**
     * @return mixed
     */
    public function getAdminDepartmentId()
    {
        return $this->adminDepartmentId;
    }

    /**
     * @param mixed $adminDepartmentId
     * @return $this
     */
    public function setAdminDepartmentId($adminDepartmentId)
    {
        $this->adminDepartmentId = $adminDepartmentId;
        return $this;
    }

    protected $categoryIds;

    /**
     * @return mixed
     */
    public function getCategoryIds()
    {
        return $this->categoryIds;
    }

    /**
     * @param mixed $categoryIds
     * @return $this
     */
    public function setCategoryIds($categoryIds)
    {
        $this->categoryIds = $categoryIds;
        return $this;
    }
}
