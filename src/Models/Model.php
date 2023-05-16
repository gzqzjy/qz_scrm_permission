<?php

namespace Qz\Admin\Access\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Model extends \Illuminate\Database\Eloquent\Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'common';
}
