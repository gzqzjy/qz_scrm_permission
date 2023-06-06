<?php


namespace Qz\Admin\Permission\Models;


class AdminDepartment extends Model
{
    protected $fillable = [
        'name',
        'pid',
        'level',
        'customer_subsystem_id'
    ];
}
