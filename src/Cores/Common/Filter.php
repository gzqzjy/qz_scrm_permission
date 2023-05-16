<?php

namespace Qz\Admin\Permission\Cores\Common;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Qz\Admin\Permission\Cores\Core;

class Filter extends Core
{
    protected function execute()
    {
        $param = $this->getParam();
        if (empty($param)) {
            return;
        }
        if (!is_array($param)) {
            return;
        }
        foreach ($param as $item) {
            $this->search($item);
        }
    }

    protected function search($item)
    {
        $field = Str::snake(Arr::get($item, 'field'));
        $option = Arr::get($item, 'option');
        $value = Arr::get($item, 'value');
        if (empty($field) || empty($option)) {
            return;
        }
        $model = $this->getModel();
        $model = $this->searchItem($model, $field, $option, $value);
        $this->setModel($model);
    }

    protected function searchItem(Builder $model, $field, $option, $value)
    {
        if (strpos($field, '.') !== false) {
            $firstField = Str::before($field, '.');
            $otherField = Str::after($field, '.');
            return $model->whereHas($firstField, function (Builder $query) use ($otherField, $option, $value) {
                return $this->searchItem($query, $otherField, $option, $value);
            });
        }
        switch ($option) {
            case '=':
            case '!=':
                if (is_array($value)) {
                    foreach ($value as $item) {
                        $model->where($field, $option, $item);
                    }
                } else {
                    $model->where($field, $option, $value);
                }
                break;
            case '>':
            case '<':
            case '>=':
            case '<=':
                $model->where($field, $option, $value);
                break;
            case 'empty':
                $model->where(function (Builder $builder) use ($field) {
                    $builder->whereNull($field)
                        ->orWhere($field, '=', '')
                        ->orWhere($field, '=', 0);
                });
                break;
            case 'not_empty':
                $model->where(function (Builder $builder) use ($field) {
                    $builder->whereNotNull($field)
                        ->orWhere($field, '!=', '')
                        ->orWhere($field, '!=', 0);
                });
                break;
            case 'contain':
                if (is_array($value)) {
                    $model->whereIn($field, $option, $value);
                } else {
                    $model->where($field, 'like', '%' . $value . '%');
                }
                break;
            case 'not_contain':
                if (is_array($value)) {
                    $model->whereNotIn($field, $option, $value);
                } else {
                    $model->where($field, 'not like', '%' . $value . '%');
                }
                break;
            case 'like':
                $model->where($field, 'like', '%' . $value . '%');
                break;
            case 'left_like':
                $model->where($field, 'like', $value . '%');
                break;
            case 'right_like':
                $model->where($field, 'like', '%' . $value);
                break;
        }
        return $model;
    }

    protected $model;

    protected $param;

    /**
     * @return Builder
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param Builder $model
     * @return $this
     */
    public function setModel(Builder $model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getParam()
    {
        return $this->param;
    }

    /**
     * @param $param
     * @return $this
     */
    public function setParam($param)
    {
        $this->param = $param;
        return $this;
    }
}
