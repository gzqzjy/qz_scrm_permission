<?php

namespace Qz\Admin\Permission\Models;

class AdminPage extends Model
{
    protected $fillable = [
        'name',
        'code',
        'subsystem_id',
    ];
}
