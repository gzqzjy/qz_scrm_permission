<?php


namespace Qz\Admin\Permission\Models;


class AdminUserCustomerSubsystemDepartment extends Model
{
    protected $fillable = [
        'admin_department_id',
        'admin_user_customer_subsystem_id',
        'administrator'
    ];

    public function adminDepartment()
    {
        return $this->belongsTo(AdminDepartment::class);
    }

    public function adminUserCustomerSubsystem()
    {
        return $this->belongsTo(AdminUserCustomerSubsystem::class);
    }
}
