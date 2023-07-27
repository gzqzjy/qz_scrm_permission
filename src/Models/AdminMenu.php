<?php

namespace Qz\Admin\Permission\Models;

class AdminMenu extends Model
{
    protected $fillable = [
        'name',
        'path',
        'parent_id',
        'sort',
        'admin_page_id',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public function adminUserMenus()
    {
        return $this->hasMany(AdminUserMenu::class);
    }

    public function parent()
    {
        return $this->belongsTo(AdminMenu::class, 'parent_id');
    }

    public function parentData()
    {
        return $this->parent()->with([
            'parentData'
        ]);
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
            'adminPageOptions',
            'adminPageColumns',
        ]);
    }

    public function adminPage()
    {
        return $this->belongsTo(AdminPage::class);
    }

    public function adminPageOptions()
    {
        return $this->hasMany(AdminPageOption::class, 'admin_page_id', 'admin_page_id')
            ->where('admin_page_id', '>', 0);
    }

    public function adminPageColumns()
    {
        return $this->hasMany(AdminPageColumn::class, 'admin_page_id', 'admin_page_id')
            ->where('admin_page_id', '>', 0);
    }

    public function childrenRequest()
    {
        return $this->child()->with([
            'children',
            'adminPage',
            'adminPage.adminPageOptions',
            'adminPage.adminPageOptions.adminRequests',
        ]);
    }

    public function adminRoleMenus()
    {
        return $this->hasMany(AdminRoleMenu::class);
    }
}
