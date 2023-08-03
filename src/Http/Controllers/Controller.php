<?php

namespace Qz\Admin\Permission\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
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
            $request->offsetSet('page', max(1, (int) $request->input('current')));
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
        $data = $this->toArray($data);
        $data = $this->int2String($data);
        return response()->json($data);
    }

    final protected function json($data = [])
    {
        $data = $this->camel($data);
        return $this->response($data);
    }

    final protected function success($data = [], $message = 'success')
    {
        if ($data instanceof Collection) {
            $data = $data->toArray();
        }
        $success = true;
        return $this->json(compact('success', 'data', 'message'));
    }

    final protected function error($message = 'error', $data = [])
    {
        $success = false;
        return $this->json(compact('success', 'data', 'message'));
    }

    final protected function toArray($value)
    {
        if ($value instanceof Model || $value instanceof Collection || $value instanceof Pivot) {
            return $value->toArray();
        }
        return $value;
    }

    final protected function int2String($value)
    {
        if (is_array($value) && !empty($value)) {
            $result = [];
            foreach ($value as $k => $v) {
                $result[$k] = $this->int2String($v);
            }
            return $result;
        } else if (is_numeric($value) && $value > 10000000000) {
            if (strpos($value, 'E') !== false) {
                return sprintf('%.0f', $value);
            }
            return (string) $value;
        }
        return $value;
    }

    final protected function camel($array)
    {
        $results = [];
        foreach ($array as $key => $value) {
            $value = $this->toArray($value);
            $camelKey = Str::camel($key);
            if (is_array($value) && !empty($value)) {
                $results[$camelKey] = $this->camel($value);
            } else if (is_numeric($value) && $value > 10000000000) {
                $results[$camelKey] = (string) $value;
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
