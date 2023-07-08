<?php

namespace Qz\Admin\Permission\Models;



use Qz\Admin\Permission\Scopes\CustomerIdScope;

class CustomerSubsystem extends Model
{
    protected $fillable = [
        'customer_id',
        'subsystem_id',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new CustomerIdScope());
    }

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
