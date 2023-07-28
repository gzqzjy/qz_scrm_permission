<?php

namespace Qz\Admin\Permission\Models;

class AdminCategoryDepartment extends Model
{
    protected $fillable = [
        'admin_department_id',
        'category_id'
    ];

    public function adminDepartment()
    {
        return $this->belongsTo(AdminDepartment::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
