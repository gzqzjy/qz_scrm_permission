<?php

namespace Qz\Admin\Permission\Models;

class AdminPage extends Model
{
    protected $fillable = [
        'name',
        'code',
    ];

    public function adminPageOptions()
    {
        return $this->hasMany(AdminPageOption::class);
    }

    public function adminPageColumns()
    {
        return $this->hasMany(AdminPageColumn::class);
    }

    public function adminMenus()
    {
        return $this->hasMany(AdminMenu::class);
    }
}
