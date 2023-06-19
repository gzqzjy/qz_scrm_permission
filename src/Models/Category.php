<?php

namespace Qz\Admin\Permission\Models;

class Category extends Model
{
    protected $fillable = [
        'name',
    ];

    public function adminCategoryDepartments()
    {
        return $this->hasMany(AdminCategoryDepartment::class);
    }

}
