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
            'adminPage',
            'adminPage.adminPageOptions',
            'adminPage.adminPageColumns',
        ]);
    }

    public function adminPage()
    {
        return $this->belongsTo(AdminPage::class);
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
}
