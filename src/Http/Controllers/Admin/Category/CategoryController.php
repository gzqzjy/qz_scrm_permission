<?php
namespace Qz\Admin\Permission\Http\Controllers\Admin\Category;

use Illuminate\Database\Eloquent\Builder;
use Qz\Admin\Permission\Http\Controllers\Admin\AdminController;
use Qz\Admin\Permission\Models\Category;

class CategoryController extends AdminController
{
    public function all()
    {
        $model = Category::query();
        $model = $this->filter($model);
        if ($this->getParam('select')) {
            $model->selectRaw($this->getParam('select'));
        }
        if ($this->getParam('admin_department_id')){
            $adminDepartmentId = $this->getParam('admin_department_id');
            $model->whereHas('adminCategoryDepartments', function (Builder $builder) use ($adminDepartmentId){
                $builder->where('admin_department_id', $adminDepartmentId);
            });
        }
        $model = $model->get();
        return $this->response($model);
    }
}
