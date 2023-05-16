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
}
