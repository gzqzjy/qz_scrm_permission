<?php
namespace Qz\Admin\Permission\Facades;

use Qz\Admin\Permission\Facades\Logic\AccessLogic;
use Illuminate\Support\Facades\Facade;

/**
 * Class Access
 * @package Qz\Admin\Permission\Facades
 * @method static void setSubsystemId($id)
 * @method static int getSubsystemId()
 * @method static void setCustomerId($id)
 * @method static int getCustomerId()
 */
class Access extends Facade
{
    protected static function getFacadeAccessor()
    {
        return AccessLogic::class;
    }
}
