<?php
namespace Qz\Admin\Permission\Facades;

use Illuminate\Support\Facades\Facade;
use Qz\Admin\Permission\Facades\Logic\RequestIdLogic;

/**
 * Class Access
 * @package Qz\Admin\Permission\Facades
 * @method static void set($id)
 * @method static string get()
 */
class RequestId extends Facade
{
    protected static function getFacadeAccessor()
    {
        return RequestIdLogic::class;
    }
}
