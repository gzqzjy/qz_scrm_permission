<?php

namespace Qz\Admin\Permission\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Qz\Admin\Permission\Facades\RequestId;

class RequestLogMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws Exception
     */
    public function handle(Request $request, Closure $next)
    {
        $requestId = now()->format('YmdHis') . Str::random(18);
        RequestId::set($requestId);
        $this->setRequestTime(now());
        $response = $next($request);
        $response->header('requestId', RequestId::get());
        try {
            $this->setResponseTime(now());
            Log::info('请求日志', [
                'requestId' => RequestId::get(),
                'ip' => $request->ip(),
                'path' => $request->path(),
                '请求时间' => $this->getRequestTime(),
                '响应时间' => $this->getResponseTime(),
                '耗时(s)' => !empty($this->getResponseTime()) && !empty($this->getRequestTime()) ? $this->getResponseTime()->diffInSeconds($this->getRequestTime()) : 0,
                '耗时(ms)' => !empty($this->getResponseTime()) && !empty($this->getRequestTime()) ? $this->getResponseTime()->getTimestampMs() - $this->getRequestTime()->getTimestampMs() : 0,
                '请求参数' => $request->all(),
                '返回值' => json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ]);
        } catch (Exception $exception) {
            Log::error('请求日志错误', [
                $exception->getMessage(),
            ]);
        }
        return $response;
    }

    protected $requestTime;

    /**
     * @return mixed
     */
    public function getRequestTime()
    {
        return $this->requestTime;
    }

    /**
     * @param mixed $requestTime
     * @return $this
     */
    public function setRequestTime($requestTime)
    {
        $this->requestTime = $requestTime;
        return $this;
    }

    protected $responseTime;

    /**
     * @return mixed
     */
    public function getResponseTime()
    {
        return $this->responseTime;
    }

    /**
     * @param mixed $responseTime
     * @return $this
     */
    public function setResponseTime($responseTime)
    {
        $this->responseTime = $responseTime;
        return $this;
    }
}
