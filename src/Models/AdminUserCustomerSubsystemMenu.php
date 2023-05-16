<?php

namespace Qz\Admin\Permission\Models;

class AdminUserCustomerSubsystemMenu extends Model
{
    protected $fillable = [
        'admin_user_custom_subsystem_id',
        'admin_menu_id'
    ];

    public function adminMenu()
    {
        return $this->belongsTo(AdminUser::class);
    }
}
