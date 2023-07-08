<?php


namespace Qz\Admin\Permission\Models;


use Illuminate\Support\Arr;

class AdminDepartment extends Model
{
    protected $fillable = [
        'name',
        'pid',
        'level',
        'customer_subsystem_id'
    ];

    public function customerSubsystem()
    {
        return $this->belongsTo(CustomerSubsystem::class);
    }

    public function adminCategoryDepartments()
    {
        return $this->hasMany(AdminCategoryDepartment::class);
    }

    public function adminDepartmentRoles()
    {
        return $this->hasMany(AdminDepartmentRole::class);
    }

    public function adminUserCustomerSubsystemDepartments()
    {
        return $this->hasMany(AdminUserCustomerSubsystemDepartment::class);
    }

    public function child()
    {
        return $this->hasMany(self::class, 'pid', 'id');
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
