<?php


namespace Qz\Admin\Permission\Models;


class AdminUserCustomerSubsystemRequestEmployee extends Model
{
    protected $fillable = [
        'admin_user_customer_subsystem_id',
        'admin_request_id',
        'permission_admin_user_customer_subsystem_id',
        'type'
    ];

    const TYPE_ADD = 'add';
    const TYPE_DELETE = 'delete';

    public $typeDesc = [
        self::TYPE_ADD => '添加',
        self::TYPE_DELETE => '删除'
    ];
    

    public function adminUserCustomerSubsystem()
    {
        return $this->belongsTo(AdminUserCustomerSubsystem::class);
    }

    public function adminRequest()
    {
        return $this->belongsTo(AdminRequest::class);
    }

}
