<?php

namespace Qz\Admin\Access\Http\Controllers;

use App\Models\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $param;

    public function __construct(Request $request = null)
    {
        if ($request) {
            $this->setParam($request->all());
        }
    }

    final protected function getParam($key = '')
    {
        if ($key) {
            return Arr::get($this->param, $key);
        }
        return $this->param;
    }

    /**
     * @param $key
     * @param $value
     * @return array
     */
    final protected function addParam($key, $value)
    {
        $this->param = Arr::add($this->param, $key, $value);
        return $this->param;
    }

    /**
     * @param mixed $param
     * @return Controller
     */
    final public function setParam($param)
    {
        $this->param = $this->snake($param);
        return $this;
    }

    final protected function response($data = [])
    {
        return response()->json($data);
    }

    final protected function json($data = [])
    {
        $data = Arr::add($data, 'success', true);
        $data = Arr::add($data, 'message', 'success');
        return $this->response($this->camel($data));
    }

    final protected function success($data = [], $message = 'success')
    {
        return $this->json(compact('data', 'message'));
    }

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
        return min(1000, max(1, (int) $this->getParam('page_size')));
    }

    final protected function camel($array)
    {
        $results = [];
        foreach ($array as $key => $value) {
            $camelKey = Str::camel($key);
            if ($value instanceof Model) {
                $value = $value->toArray();
            }
            if (is_array($value) && !empty($value)) {
                $results[$camelKey] = $this->camel($value);
            } else {
                $results[$camelKey] = $value;
            }
        }
        return $results;
    }

    final protected function snake($array)
    {
        $results = [];
        foreach ($array as $key => $value) {
            $snakeKey = Str::snake($key);
            if (is_array($value) && !empty($value)) {
                $results[$snakeKey] = $this->snake($value);
            } else {
                $results[$snakeKey] = $value;
            }
        }
        return $results;
    }
}
