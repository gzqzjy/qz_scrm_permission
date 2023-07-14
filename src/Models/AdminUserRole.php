<?php

namespace Qz\Admin\Permission\Models;

class AdminUserRole extends Model
{
    protected $fillable = [
        'admin_role_id',
        'admin_user_id',
    ];

    public function adminRole()
    {
        return $this->belongsTo(AdminRole::class);
    }

    public function adminUser()
    {
        return $this->belongsTo(AdminUser::class);
    }
}
