<?php

namespace Qz\Admin\Permission\Http\Controllers\Admin;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
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
        return $this->success($data);
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

    protected function isAdministrator()
    {
        return Access::getAdministrator();
    }

    protected function getLoginAdminUserId()
    {
        return Auth::guard('admin')->id();
    }
}
