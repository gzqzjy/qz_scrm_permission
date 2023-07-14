<?php

namespace Qz\Admin\Permission\Models;

class AdminDepartmentRole extends Model
{
    protected $fillable = [
        'admin_department_id',
        'admin_role_id'
    ];

    public function adminDepartment()
    {
        return $this->belongsTo(AdminDepartment::class);
    }

    public function adminRole()
    {
        return $this->belongsTo(AdminRole::class);
    }
}
