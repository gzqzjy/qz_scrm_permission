<?php

namespace Qz\Admin\Permission\Models;

class AdminMenu extends Model
{
    protected $fillable = [
        'name',
        'path',
        'subsystem_id',
        'parent_id',
        'sort',
        'admin_page_id',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public function adminUserCustomerSubsystemMenus()
    {
        return $this->hasMany(AdminUserCustomerSubsystemMenu::class);
    }

    public function parent()
    {
        return $this->belongsTo(AdminMenu::class, 'parent_id');
    }

    public function child()
    {
        return $this->hasMany(AdminMenu::class, 'parent_id', 'id')
            ->orderByDesc('sort');
    }

    public function children()
    {
        return $this->child()->with([
            'children',
            'adminPage',
            'adminPage.adminPageOptions',
            'adminPage.adminPageColumns',
        ]);
    }

    public function adminPage()
    {
        return $this->belongsTo(AdminPage::class);
    }
}
