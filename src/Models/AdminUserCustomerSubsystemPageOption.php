<?php

namespace Qz\Admin\Access\Models;

class AdminUserCustomerSubsystemPageOption extends Model
{
    protected $fillable = [
        'admin_user_id',
        'customer_id',
        'subsystem_id',
        'admin_page_option_id',
    ];

    public function adminUser()
    {
        return $this->belongsTo(AdminUser::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function subsystem()
    {
        return $this->belongsTo(Subsystem::class);
    }

    public function adminPageOption()
    {
        return $this->belongsTo(AdminPageOption::class);
    }
}
