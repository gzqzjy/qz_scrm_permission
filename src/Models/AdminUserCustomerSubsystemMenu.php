<?php

namespace Qz\Admin\Permission\Models;

class AdminUserCustomerSubsystemMenu extends Model
{
    protected $fillable = [
        'admin_user_customer_subsystem_id',
        'admin_menu_id'
    ];

    public function adminMenu()
    {
        return $this->belongsTo(AdminMenu::class);
    }

    public function adminUserCustomerSubsystem()
    {
        return $this->belongsTo(AdminUserCustomerSubsystem::class);
    }
}
