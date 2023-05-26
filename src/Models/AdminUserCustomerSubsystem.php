<?php

namespace Qz\Admin\Permission\Models;

class AdminUserCustomerSubsystem extends Model
{
    protected $fillable = [
        'admin_user_id',
        'customer_subsystem_id',
        'status',
        'administrator',
    ];

    const STATUS_NORMAL = 'normal';

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
}
