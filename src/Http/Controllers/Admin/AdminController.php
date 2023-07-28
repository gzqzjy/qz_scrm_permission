<?php

namespace Qz\Admin\Permission\Http\Controllers\Admin;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Qz\Admin\Permission\Cores\Auth\CategoryIdsGet;
use Qz\Admin\Permission\Cores\Common\Filter;
use Qz\Admin\Permission\Facades\Access;
use Qz\Admin\Permission\Http\Controllers\Controller;

class AdminController extends Controller
{
    final protected function page(LengthAwarePaginator $paginator)
    {
        $data = [
            'data' => $paginator->items(),
            'total' => $paginator->total(),
            'pageSize' => $paginator->perPage(),
            'current' => $paginator->currentPage(),
        ];
        return $this->json($this->camel($data));
    }

    final protected function getPageSize()
    {
        return min(1000, max(1, (int) $this->getParam('page_size', 20)));
    }

    protected function filter(Builder $model)
    {
        $model = Filter::init()
            ->setModel($model)
            ->setParam($this->getParam('filter'))
            ->run()
            ->getModel();
        $sort = $this->getParam('sort');
        if (!empty($sort) && is_array($sort)) {
            foreach ($sort as $key => $value) {
                if ($value == 'ascend') {
                    $model->orderBy($key);
                } elseif ($value == 'descend') {
                    $model->orderByDesc($key);
                }
            }
        }
        return $model;
    }

    protected function getChildFilter()
    {
        $filter = [];
        if ($this->getParam('filter')) {
            foreach ($this->getParam('filter') as $item) {
                $field = Str::snake(Arr::get($item, 'field'));
                if (strpos($field, '.') !== false) {
                    $firstField = Str::beforeLast($field, '.');
                    $otherField = Str::afterLast($field, '.');
                    $firstField = Str::camel($firstField);
                    $item['field'] = $otherField;
                    $filter[$firstField][] = $item;
                }
            }
        }
        return $filter;
    }

    protected function isAdministrator()
    {
        return Access::getAdministrator();
    }

    protected function getLoginAdminUserId()
    {
        return Auth::guard('admin')->id();
    }

    protected function getCustomerId()
    {
        return Access::getCustomerId();
    }

    protected function getLoginCategoryIdes()
    {
        return (array) CategoryIdsGet::init()
            ->setAdminUserId($this->getLoginAdminUserId())
            ->run()
            ->getIds();
    }
}
