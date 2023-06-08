<?php

namespace Qz\Admin\Permission\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Model extends \Illuminate\Database\Eloquent\Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'common';

    public function serializeDate($date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
