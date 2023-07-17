<?php

namespace Qz\Admin\Permission\Models;

use Qz\Admin\Permission\Scopes\CustomerIdScope;

class AdminRole extends Model
{
    protected $fillable = [
        'name',
        'admin_role_group_id',
        'customer_id',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new CustomerIdScope());
    }

    public function adminRoleGroup()
    {
        return $this->belongsTo(AdminRoleGroup::class);
    }

    public function adminDepartmentRoles()
    {
        return $this->hasMany(AdminDepartmentRole::class);
    }

    public function adminUserRoles()
    {
        return $this->hasMany(AdminUserRole::class);
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
