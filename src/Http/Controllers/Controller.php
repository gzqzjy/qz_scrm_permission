<?php

namespace Qz\Admin\Permission\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
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
            $request->offsetSet('page', max(1, (int)$request->input('current')));
            $this->setParam($request->all());
        }
    }

    final protected function getParam($key = '', $default = null)
    {
        if ($key) {
            return Arr::get($this->param, $key, $default);
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
        return $this->response($this->camel($data));
    }

    final protected function success($data = [], $message = 'success')
    {
        $success = true;
        return $this->json(compact('success', 'data', 'message'));
    }

    final protected function error($message = 'error', $data = [])
    {
        $success = false;
        return $this->json(compact('success', 'data', 'message'));
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
