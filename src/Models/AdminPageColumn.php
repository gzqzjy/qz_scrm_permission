<?php

namespace Qz\Admin\Permission\Models;

class AdminPageColumn extends Model
{
    protected $fillable = [
        'admin_page_id',
        'name',
        'code',
    ];

    public function adminPage()
    {
        return $this->belongsTo(AdminPage::class);
    }

    public function adminUserPageColumns()
    {
        return $this->hasMany(AdminUserPageColumn::class);
    }
}
