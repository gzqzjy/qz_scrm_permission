<?php
namespace Qz\Admin\Permission\Cores\AdminCategoryDepartment;

use Illuminate\Support\Arr;
use Qz\Admin\Permission\Cores\Core;
use Qz\Admin\Permission\Models\AdminCategoryDepartment;

class AdminCategoryDepartmentAdd extends Core
{
    protected function execute()
    {
        $model = AdminCategoryDepartment::withTrashed()
            ->firstOrCreate(Arr::whereNotNull([
                'category_id' => $this->getCategoryId(),
                'admin_department_id' => $this->getAdminDepartmentId(),
            ]));
        if ($model->trashed()) {
            $model->restore();
        }
        $this->setId($model->getKey());
    }
    
    protected $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return AdminCategoryDepartmentAdd
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    
    

    protected $categoryId;

    /**
     * @return mixed
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * @param mixed $categoryId
     * @return AdminCategoryDepartmentAdd
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;
        return $this;
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
     * @return AdminCategoryDepartmentAdd
     */
    public function setAdminDepartmentId($adminDepartmentId)
    {
        $this->adminDepartmentId = $adminDepartmentId;
        return $this;
    }


}
