<?php

namespace Qz\Admin\Permission;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Qz\Admin\Permission\Http\Controllers\Admin\Auth\V1\AccessController;

class AdminPermissionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->defineRoutes();
    }

    protected function defineRoutes()
    {
        Route::post('/test', AccessController::class . '@login');
    }
}
