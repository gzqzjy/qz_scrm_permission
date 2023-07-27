<?php

namespace Qz\Admin\Permission\Models;

use Illuminate\Support\Arr;
use Qz\Admin\Permission\Scopes\CustomerIdScope;

class AdminDepartment extends Model
{
    protected $connection = 'common';

    protected $fillable = [
        'name',
        'pid',
        'level',
        'customer_id',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new CustomerIdScope());
    }

    public function adminCategoryDepartments()
    {
        return $this->hasMany(AdminCategoryDepartment::class);
    }

    public function adminDepartmentRoles()
    {
        return $this->hasMany(AdminDepartmentRole::class);
    }

    public function adminUserDepartments()
    {
        return $this->hasMany(AdminUserDepartment::class);
    }

    public function child()
    {
        return $this->hasMany(self::class, 'pid', 'id')->with([
            'adminUserDepartments',
            'adminCategoryDepartments',
            'adminDepartmentRoles',
            'adminUserDepartments.adminUser',
            'adminUserDepartments.adminUser.adminUserRoles',
            'adminUserDepartments.adminUser.adminUserRoles.adminRole',
            'adminUserDepartments.adminUser.adminUserDepartments',
        ])->withCount([
            'adminUserDepartments',
            'adminCategoryDepartments',
            'adminDepartmentRoles',
        ]);
    }

    public function children()
    {
        return $this->child()->with([
            'children'
        ]);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'pid');
    }
}
