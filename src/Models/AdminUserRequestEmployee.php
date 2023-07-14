<?php

namespace Qz\Admin\Permission\Models;

class AdminUserRequestEmployee extends Model
{
    protected $fillable = [
        'admin_user_id',
        'admin_request_id',
        'permission_admin_user_id',
        'type'
    ];

    const TYPE_ADD = 'add';
    const TYPE_DELETE = 'delete';

    public $typeDesc = [
        self::TYPE_ADD => '添加',
        self::TYPE_DELETE => '删除'
    ];

    public function adminUser()
    {
        return $this->belongsTo(AdminUser::class);
    }

    public function adminRequest()
    {
        return $this->belongsTo(AdminRequest::class);
    }
}
