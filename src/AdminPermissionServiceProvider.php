<?php

namespace Qz\Admin\Permission;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

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
            'app.timezone' => 'Asia/Shanghai',
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
                ])->namespace('Auth\V1')
                    ->group(function () {
                        Route::post('login/account', 'AccessController@login');
                        Route::post('login/captcha', 'AccessController@captcha');
                        Route::post('access/option', 'AccessController@option');
                        Route::post('access/options', 'AccessController@options');
                        Route::post('access/columns', 'AccessController@columns');
                        Route::post('menu', 'AccessController@menu');
                    });
                Route::namespace('Auth\V1')
                    ->group(function () {
                        Route::post('user', 'AccessController@user');
                        Route::post('logout', 'AccessController@logout');
                    });
                Route::namespace('Category\V1')->group(function () {
                    Route::post('categories/all', 'CategoryController@all');
                });
                Route::namespace('AdminPage\V1')->group(function () {
                    Route::post('admin-pages/get', 'AdminPageController@get');
                    Route::post('admin-pages/add', 'AdminPageController@store');
                    Route::post('admin-pages/update', 'AdminPageController@update');
                    Route::post('admin-pages/delete', 'AdminPageController@destroy');
                    Route::post('admin-pages/all', 'AdminPageController@all');
                });
                Route::namespace('AdminPageOption\V1')->group(function () {
                    Route::post('admin-page-options/all', 'AdminPageOptionController@all');
                });
                Route::namespace('AdminPageColumn\V1')->group(function () {
                    Route::post('admin-page-columns/all', 'AdminPageColumnController@all');
                });
                Route::namespace('AdminMenu\V1')->group(function () {
                    Route::post('admin-menus/get', 'AdminMenuController@get');
                    Route::post('admin-menus/add', 'AdminMenuController@store');
                    Route::post('admin-menus/update', 'AdminMenuController@update');
                    Route::post('admin-menus/delete', 'AdminMenuController@destroy');
                    Route::post('admin-menus/all', 'AdminMenuController@all');
                    Route::post('admin-menus/cascader', 'AdminMenuController@cascader');
                });
                Route::namespace('AdminUser\V1')->group(function () {
                    Route::post('admin-users/get', 'AdminUserController@get');
                    Route::post('admin-users/add', 'AdminUserController@store');
                    Route::post('admin-users/update', 'AdminUserController@update');
                    Route::post('admin-users/delete', 'AdminUserController@destroy');
                    Route::post('admin-users/all', 'AdminUserController@all');
                    Route::post('admin-users/all-status', 'AdminUserController@allStatus');
                    Route::post('admin-users/all-sex', 'AdminUserController@allSex');
                    Route::post('admin-users/add-menus', 'AdminUserController@addMenus');
                    Route::post('admin-users/permission', 'AdminUserController@permission');
                    Route::post('admin-users/update-permission', 'AdminUserController@updatePermission');
                });
                Route::namespace('AdminUserCustomerSubsystem\V1')->group(function () {
                    Route::post('admin-user-customer-subsystems/get', 'AdminUserCustomerSubsystemController@get');
                    Route::post('admin-user-customer-subsystems/add', 'AdminUserCustomerSubsystemController@store');
                    Route::post('admin-user-customer-subsystems/update', 'AdminUserCustomerSubsystemController@update');
                    Route::post('admin-user-customer-subsystems/delete', 'AdminUserCustomerSubsystemController@destroy');
                    Route::post('admin-user-customer-subsystems/all', 'AdminUserCustomerSubsystemController@all');
                    Route::post('admin-user-customer-subsystems/add-menus', 'AdminUserCustomerSubsystemController@addMenus');
                });
                Route::namespace('AdminRoleGroup\V1')->group(function () {
                    Route::post('admin-role-groups/get', 'AdminRoleGroupController@getByRole');
                    Route::post('admin-role-groups/add', 'AdminRoleGroupController@store');
                    Route::post('admin-role-groups/update', 'AdminRoleGroupController@update');
                    Route::post('admin-role-groups/delete', 'AdminRoleGroupController@destroy');
                    Route::post('admin-role-groups/all', 'AdminRoleGroupController@all');
                    Route::post('admin-role-groups/all-by-role', 'AdminRoleGroupController@allByRole');
                });
                Route::namespace('AdminRole\V1')->group(function () {
                    Route::post('admin-roles/get', 'AdminRoleController@get');
                    Route::post('admin-roles/add', 'AdminRoleController@store');
                    Route::post('admin-roles/update', 'AdminRoleController@update');
                    Route::post('admin-roles/delete', 'AdminRoleController@destroy');
                    Route::post('admin-roles/all', 'AdminRoleController@all');
                    Route::post('admin-roles/permission', 'AdminRoleController@permission');
                });
                Route::namespace('AdminDepartment\V1')->group(function () {
                    Route::post('admin-departments/get', 'AdminDepartmentController@get');
                    Route::post('admin-departments/add', 'AdminDepartmentController@store');
                    Route::post('admin-departments/update', 'AdminDepartmentController@update');
                    Route::post('admin-departments/delete', 'AdminDepartmentController@destroy');
                    Route::post('admin-departments/all', 'AdminDepartmentController@all');
                });
            });
    }

    protected function configureMiddleware()
    {
    }
}
