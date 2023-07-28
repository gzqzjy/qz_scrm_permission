<?php

namespace Qz\Admin\Permission\Models;

class AdminRolePageOption extends Model
{
    protected $fillable = [
        'admin_role_id',
        'admin_page_option_id'
    ];

    public function adminPageOption()
    {
        return $this->belongsTo(AdminPageOption::class);
    }

    public function adminRole()
    {
        return $this->belongsTo(AdminRole::class);
    }
}
