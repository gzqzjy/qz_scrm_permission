<?php
namespace Qz\Admin\Access\Facades;

use Qz\Admin\Access\Facades\Logic\AccessLogic;
use Illuminate\Support\Facades\Facade;

/**
 * Class Access
 * @package Qz\Admin\Access\Facades
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
