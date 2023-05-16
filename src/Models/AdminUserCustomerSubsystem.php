<?php

namespace Qz\Admin\Permission\Models;

class AdminUserCustomerSubsystem extends Model
{
    protected $fillable = [
        'admin_user_id',
        'custom_subsystem_id',
    ];

    const STATUS_NORMAL = 'normal';
}
