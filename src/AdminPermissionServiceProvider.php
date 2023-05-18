<?php

namespace Qz\Admin\Permission;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Qz\Admin\Permission\Http\Controllers\Admin\Auth\V1\AccessController;
use Qz\Admin\Permission\Http\Middleware\AccessMiddleware;

class AdminPermissionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        config([
            'database.connections' => array_merge([
                'common' => [
                    'driver' => 'mysql',
                    'url' => env('DATABASE_URL'),
                    'host' => env('DB_HOST', '127.0.0.1'),
                    'port' => env('DB_PORT', '3306'),
                    'database' => env('DB_DATABASE', 'forge'),
                    'username' => env('DB_USERNAME', 'forge'),
                    'password' => env('DB_PASSWORD', ''),
                    'unix_socket' => env('DB_SOCKET', ''),
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                    'prefix_indexes' => true,
                    'strict' => true,
                    'engine' => null,
                    'options' => extension_loaded('pdo_mysql') ? array_filter([
                        \PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                    ]) : [],
                ],
            ], config('database.connections', [])),
            'auth.providers' => array_merge([
                'admin_users' => [
                    'driver' => 'eloquent',
                    'model' => \Qz\Admin\Permission\Models\AdminUser::class,
                ],
            ], config('auth.providers', [])),
            'auth.guards' => array_merge([
                'admin' => [
                    'driver' => 'sanctum',
                    'provider' => 'admin_users',
                ],
            ], config('auth.guards', [])),
        ]);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureMiddleware();
        $this->adminV1Routes();
    }

    protected function adminV1Routes()
    {
        Route::prefix('/admin/v1')
            ->middleware([
                'auth:admin',
                'access',
            ])
            ->namespace('Qz\Admin\Permission\Http\Controllers\Admin')
            ->group(function () {
                Route::withoutMiddleware([
                    'auth:admin',
                    'admin.access',
                ])->namespace('Auth\V1')
                    ->group(function () {
                        Route::post('login/account', 'AccessController@login');
                        Route::post('login/captcha', 'AccessController@captcha');
                        Route::post('access/option', 'AccessController@option');
                        Route::post('access/columns', 'AccessController@columns');
                        Route::post('menu', 'AccessController@menu');
                    });
                Route::namespace('Auth\V1')
                    ->group(function () {
                        Route::post('user', 'AccessController@user');
                        Route::post('logout', 'AccessController@logout');
                    });
                Route::namespace('AdminPage\V1')->group(function () {
                    Route::post('admin-pages/get', 'AdminPageController@get');
                    Route::post('admin-pages/add', 'AdminPageController@store');
                    Route::post('admin-pages/update', 'AdminPageController@update');
                    Route::post('admin-pages/delete', 'AdminPageController@destroy');
                    Route::post('admin-pages/all', 'AdminPageController@all');
                });
                Route::namespace('AdminMenu\V1')->group(function () {
                    Route::post('admin-menus/get', 'AdminMenuController@get');
                    Route::post('admin-menus/add', 'AdminMenuController@store');
                    Route::post('admin-menus/update', 'AdminMenuController@update');
                    Route::post('admin-menus/delete', 'AdminMenuController@destroy');
                });
            });
    }

    protected function configureMiddleware()
    {
    }
}
