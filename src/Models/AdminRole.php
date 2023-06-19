<?php


namespace Qz\Admin\Permission\Models;


class AdminRole extends Model
{
    protected $fillable = [
        'name',
        'admin_role_group_id',
        'customer_subsystem_id'
    ];

    public function adminRoleGroup()
    {
        return $this->belongsTo(AdminRoleGroup::class);
    }

    public function customerSubsystem()
    {
        return $this->belongsTo(CustomerSubsystem::class);
    }

    public function departmentRoles()
    {
        return $this->hasMany(AdminDepartmentRole::class);
    }

    public function adminUserCustomerSubsystemRoles()
    {
        return $this->hasMany(AdminUserCustomerSubsystemRole::class);
    }

    public function adminRoleMenus()
    {
        return $this->hasMany(AdminRoleMenu::class);
    }

    public function adminRolePageColumns()
    {
        return $this->hasMany(AdminRolePageColumn::class);
    }

    public function adminRolePageOptions()
    {
        return $this->hasMany(AdminRolePageOption::class);
    }
}
