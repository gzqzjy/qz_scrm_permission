<?php

namespace Qz\Admin\Permission\Models;

class AdminPage extends Model
{
    protected $fillable = [
        'name',
        'code',
        'subsystem_id',
    ];

    public function adminPageOptions()
    {
        return $this->hasMany(AdminPageOption::class);
    }

    public function adminPageColumns()
    {
        return $this->hasMany(AdminPageColumn::class);
    }
}
