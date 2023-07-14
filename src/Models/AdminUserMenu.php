<?php

namespace Qz\Admin\Permission\Models;

class AdminUserMenu extends Model
{
    protected $fillable = [
        'admin_user_id',
        'admin_menu_id',
        'type',
    ];

    const TYPE_ADD = 'add';
    const TYPE_DELETE = 'delete';

    public $menuTypes = [
        self::TYPE_ADD => '添加',
        self::TYPE_DELETE => '删除'
    ];

    public function adminMenu()
    {
        return $this->belongsTo(AdminMenu::class);
    }

    public function adminUser()
    {
        return $this->belongsTo(AdminUser::class);
    }
}
