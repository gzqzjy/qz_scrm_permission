<?php


namespace Qz\Admin\Permission\Models;


class AdminRoleGroup extends Model
{
    protected $fillable = [
        'name',
        'customer_subsystem_id'
    ];

    public function customerSubsystem()
    {
        return $this->belongsTo(CustomerSubsystem::class);
    }

    public function adminRoles()
    {
        return $this->hasMany(AdminRole::class);
    }
    
}
