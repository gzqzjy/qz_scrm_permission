<?php

namespace Qz\Admin\Permission\Models;

class AdminUserDepartment extends Model
{
    protected $fillable = [
        'admin_department_id',
        'admin_user_id',
        'administrator'
    ];

    public function adminDepartment()
    {
        return $this->belongsTo(AdminDepartment::class);
    }

    public function adminUser()
    {
        return $this->belongsTo(AdminUser::class);
    }
}
