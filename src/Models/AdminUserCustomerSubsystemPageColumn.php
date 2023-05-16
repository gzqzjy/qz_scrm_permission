<?php

namespace Qz\Admin\Permission\Models;

class AdminUserCustomerSubsystemPageColumn extends Model
{
    protected $fillable = [
        'admin_user_custom_subsystem_id',
        'admin_page_column_id',
    ];

    public function adminPageColumn()
    {
        return $this->belongsTo(AdminPageColumn::class);
    }
}
