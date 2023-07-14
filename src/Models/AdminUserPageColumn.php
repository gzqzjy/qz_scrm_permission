<?php

namespace Qz\Admin\Permission\Models;

class AdminUserPageColumn extends Model
{
    protected $fillable = [
        'admin_user_id',
        'admin_page_column_id',
        'type',
    ];

    public function adminPageColumn()
    {
        return $this->belongsTo(AdminPageColumn::class);
    }

    public function adminUser()
    {
        return $this->belongsTo(AdminUser::class);
    }
}
