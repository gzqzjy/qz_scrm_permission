<?php
namespace Qz\Admin\Permission\Facades;

use Qz\Admin\Permission\Facades\Logic\AccessLogic;
use Illuminate\Support\Facades\Facade;

/**
 * Class Access
 * @package Qz\Admin\Permission\Facades
 * @method static void setAdminUserId($id)
 * @method static int getAdminUserId()
 * @method static void setCustomerId($id)
 * @method static int getCustomerId()
 * @method static void setAdministrator($administrator)
 * @method static boolean getAdministrator()
 * @method static void setAdminPageOptionId($id)
 * @method static int getAdminPageOptionId()
 * @method static void setAdminPageId($id)
 * @method static int getAdminPageId()
 * @method static void setAdminRequestId($id)
 * @method static int getAdminRequestId()
 */
class Access extends Facade
{
    protected static function getFacadeAccessor()
    {
        return AccessLogic::class;
    }
}
