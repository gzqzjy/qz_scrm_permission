<?php

namespace Qz\Admin\Access\Models;

class AdminUser extends Model
{
    protected $fillable = [
        'name',
        'mobile',
        'status',
    ];
}
