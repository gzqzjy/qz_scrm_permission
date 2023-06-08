<?php

namespace Qz\Admin\Permission\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Support\Arr;
use Laravel\Sanctum\HasApiTokens;

class AdminUser extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasApiTokens;

    protected $fillable = [
        'name',
        'mobile',
        'status',
    ];

    const STATUS_WORKING = 1;
    const STATUS_LEAVING = 2;
    const STATUS_LEAVED = 3;

    const STATUS_DESC = [
        self::STATUS_WORKING => '在职',
        self::STATUS_LEAVING => '离职中',
        self::STATUS_LEAVED => '离职',
    ];

    public function getStatusDescAttribute()
    {
        return Arr::get(self::STATUS_DESC, $this->getOriginal('status'), '');
    }
    
    public function adminUserCustomerSubsystems()
    {
        return $this->hasMany(AdminUserCustomerSubsystem::class);
    }
}
