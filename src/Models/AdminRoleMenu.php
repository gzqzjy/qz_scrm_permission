<?php


namespace Qz\Admin\Permission\Models;


class AdminRoleMenu extends Model
{
    protected $fillable = [
        'admin_role_id',
        'admin_menu_id'
    ];

    public function adminMenu()
    {
        return $this->belongsTo(AdminMenu::class);
    }

    public function adminRole()
    {
        return $this->belongsTo(AdminRole::class);
    }
}
