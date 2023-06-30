<?php

namespace Qz\Admin\Permission\Models;

use Illuminate\Database\Eloquent\Builder;

class AdminPageOption extends Model
{
    protected $fillable = [
        'admin_page_id',
        'name',
        'code',
        'is_show',
    ];

    const IS_SHOW_TRUE = 1;
    const IS_SHOW_FALSE = 0;


    public function adminPage()
    {
        return $this->belongsTo(AdminPage::class);
    }

    public function adminRequests()
    {
        return $this->hasMany(AdminRequest::class);
    }

    protected static function booted()
    {
        static::addGlobalScope('isShow', function (Builder $builder) {
            return $builder->where('is_show', self::IS_SHOW_TRUE);
        });
    }
}
