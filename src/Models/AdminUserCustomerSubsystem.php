<?php

namespace Qz\Admin\Access\Models;

class AdminUserCustomerSubsystem extends Model
{
    protected $fillable = [
        'admin_user_id',
        'custom_id',
        'subsystem_id',
    ];
}
