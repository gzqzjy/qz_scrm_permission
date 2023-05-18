<?php

namespace Qz\Admin\Permission\Models;

class AdminUserCustomerSubsystemPageOption extends Model
{
    protected $fillable = [
        'admin_user_customer_subsystem_id',
        'admin_page_option_id',
    ];

    public function adminPageOption()
    {
        return $this->belongsTo(AdminPageOption::class);
    }
    
    public function adminUserCustomerSubsystem()
    {
        return $this->belongsTo(AdminUserCustomerSubsystem::class);
    }
}
