<?php

namespace Qz\Admin\Permission\Models;

class AdminUserCustomerSubsystemPageOption extends Model
{
    protected $fillable = [
        'admin_user_custom_subsystem_id',
        'admin_page_option_id',
    ];

    public function adminPageOption()
    {
        return $this->belongsTo(AdminPageOption::class);
    }
}
