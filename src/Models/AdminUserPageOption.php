<?php

namespace Qz\Admin\Permission\Models;

class AdminUserPageOption extends Model
{
    protected $fillable = [
        'admin_user_id',
        'admin_page_option_id',
        'type',
    ];

    public function adminPageOption()
    {
        return $this->belongsTo(AdminPageOption::class);
    }

    public function adminUser()
    {
        return $this->belongsTo(AdminUser::class);
    }
}
