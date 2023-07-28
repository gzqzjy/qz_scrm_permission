<?php

namespace Qz\Admin\Permission\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Model extends \Illuminate\Database\Eloquent\Model
{
    use HasFactory, SoftDeletes;

    public function __construct(array $attributes = [])
    {
        $this->setTable($this->getConnection()->getDatabaseName() . '.' . $this->getTable());
        parent::__construct($attributes);
    }

    public function serializeDate($date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
