<?php


namespace Qz\Admin\Permission\Models;


class AdminUserCustomerSubsystemRole extends Model
{
    protected $fillable = [
        'name',
        'admin_role_id',
        'admin_user_customer_subsystem_id'
    ];

    public function adminRole()
    {
        return $this->belongsTo(AdminRole::class);
    }

    public function adminUserCustomerSubsystem()
    {
        return $this->belongsTo(AdminUserCustomerSubsystem::class);
    }
}
