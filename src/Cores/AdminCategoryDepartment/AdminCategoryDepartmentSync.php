<?php
namespace Qz\Admin\Permission\Cores\AdminCategoryDepartment;

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
        // 删除多余数据
        AdminCategoryDepartment::query()
            ->where('admin_department_id', $this->getAdminDepartmentId())
            ->whereNotIn('category_id', $this->getCategoryIds())
            ->delete();
        // 恢复已删除数据
        AdminCategoryDepartment::onlyTrashed()
            ->where('admin_department_id', $this->getAdminDepartmentId())
            ->whereIn('category_id', $this->getCategoryIds())
            ->restore();
        // 添加新数据
        $oldIds = AdminCategoryDepartment::query()
            ->where('admin_department_id', $this->getAdminDepartmentId())
            ->pluck('category_id')
            ->toArray();
        $addIds = array_diff($this->getCategoryIds(), $oldIds);
        foreach ($addIds as $addId) {
            AdminCategoryDepartment::query()->create([
                'category_id' => $addId,
                'admin_department_id' => $this->getAdminDepartmentId(),
            ]);
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
