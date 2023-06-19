<?php


namespace Qz\Admin\Permission\Models;


class AdminRolePageColumn extends Model
{
    protected $fillable = [
        'admin_role_id',
        'admin_page_column_id'
    ];

    public function adminPageColumn()
    {
        return $this->belongsTo(AdminPageColumn::class);
    }

    public function adminRole()
    {
        return $this->belongsTo(AdminRole::class);
    }
}
