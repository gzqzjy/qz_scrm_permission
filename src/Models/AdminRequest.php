<?php


namespace Qz\Admin\Permission\Models;


class AdminRequest extends Model
{
    protected $fillable = [
        'admin_page_option_id',
        'name',
        'code'
    ];

    public function adminPageOption()
    {
        return $this->belongsTo(AdminPageOption::class);
    }
}
