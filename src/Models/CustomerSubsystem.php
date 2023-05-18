<?php

namespace Qz\Admin\Permission\Models;

class CustomerSubsystem extends Model
{
    protected $fillable = [
        'customer_id',
        'subsystem_id',
    ];

    public function adminUserCustomerSubsystems()
    {
        return $this->hasMany(AdminUserCustomerSubsystem::class);
    }
    
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function subsystem()
    {
        return $this->belongsTo(Subsystem::class);
    }
}
