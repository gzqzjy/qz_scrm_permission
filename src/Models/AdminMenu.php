<?php

namespace Qz\Admin\Access\Models;

class AdminMenu extends Model
{
    protected $fillable = [
        'name',
        'path',
        'parent_id',
        'sort',
        'admin_page_id',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
    ];
}
