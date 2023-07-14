<?php

namespace Qz\Admin\Permission\Models;

class Administrator extends Model
{
    protected $fillable = [
        'admin_user_id'  
    ];
    
    protected function adminUser()
    {
        return $this->belongsTo(AdminUser::class);
    }
}
