<?php

namespace Qz\Admin\Permission\Models;

class AdminUser extends Model
{
    protected $fillable = [
        'name',
        'mobile',
        'status',
    ];

    const STATUS_NORMAL = 'normal';

    public function adminUserCustomerSubsystem()
    {
        return $this->hasOne(AdminUserCustomerSubsystem::class);
    }
}
