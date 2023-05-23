<?php

namespace Qz\Admin\Permission\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Laravel\Sanctum\HasApiTokens;

class AdminUser extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasApiTokens;

    protected $fillable = [
        'name',
        'mobile',
    ];

    public function adminUserCustomerSubsystems()
    {
        return $this->hasMany(AdminUserCustomerSubsystem::class);
    }
}
