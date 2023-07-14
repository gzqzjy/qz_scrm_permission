<?php

namespace Qz\Admin\Permission\Models;

use Qz\Admin\Permission\Scopes\CustomerIdScope;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Support\Arr;
use Laravel\Sanctum\HasApiTokens;

class AdminUser extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasApiTokens;

    protected $connection = 'common';

    protected $fillable = [
        'name',
        'mobile',
        'sex',
        'status',
        'customer_id',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new CustomerIdScope());
    }

    const STATUS_WORKING = 'work';
    const STATUS_LEAVING = 'leaving';
    const STATUS_LEAVED = 'leaved';

    const STATUS_DESC = [
        self::STATUS_WORKING => '在职',
        self::STATUS_LEAVING => '离职中',
        self::STATUS_LEAVED => '离职',
    ];
    
    const SEX_UNKNOWN = 'unknown';
    const SEX_MAN = 'man';
    const SEX_WOMAN = 'woman';
    const SEX_DESC = [
        self::SEX_UNKNOWN => '未知',
        self::SEX_MAN => '男',
        self::SEX_WOMAN => '女',
    ];

    protected $appends = ['statusDesc'];

    public function getStatusDescAttribute()
    {
        return Arr::get(self::STATUS_DESC, $this->getOriginal('status'), '');
    }

    public function getSexDescAttribute()
    {
        return Arr::get(self::STATUS_DESC, $this->getOriginal('sex'), '');
    }

    public function adminUserRoles()
    {
        return $this->hasMany(AdminUserRole::class);
    }

    public function adminUserDepartments()
    {
        return $this->hasMany(AdminUserDepartment::class);
    }
    
    public function administrator()
    {
        return $this->hasOne(Administrator::class);
    }
}
