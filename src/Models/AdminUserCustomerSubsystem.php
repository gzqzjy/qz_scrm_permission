<?php

namespace Qz\Admin\Permission\Models;

class AdminUserCustomerSubsystem extends Model
{
    protected $fillable = [
        'admin_user_id',
        'customer_subsystem_id',
        'status',
        'administrator',
        'name',
        'sex',
    ];

    const STATUS_NORMAL = 'normal';
    const STATUS_LEAVING = 'leaving';
    const STATUS_LEAVED = 'leaved';

    const STATUS_DESC = [
        self::STATUS_NORMAL => '在职',
        self::STATUS_LEAVING => '离职中',
        self::STATUS_LEAVED => '离职',
    ];

    public function customerSubsystem()
    {
        return $this->belongsTo(CustomerSubsystem::class);
    }

    public function adminUser()
    {
        return $this->belongsTo(AdminUser::class);
    }

    public function adminUserCustomerSubsystemMenus()
    {
        return $this->hasMany(AdminUserCustomerSubsystemMenu::class);
    }

    public function adminUserCustomerSubsystemDepartments()
    {
        return $this->hasMany(AdminUserCustomerSubsystemDepartment::class);
    }

    public function adminUserCustomerSubsystemRoles()
    {
        return $this->hasMany(AdminUserCustomerSubsystemRole::class);
    }
}
