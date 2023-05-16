<?php

use Illuminate\Support\Facades\Route;

Route::prefix('/admin/v1')
    ->middleware([
        'auth:admin',
    ])
    ->namespace('\\Qz\\Admin\\Permission\\Http\\Controllers\\Admin\\')
    ->group(function () {
        Route::withoutMiddleware('auth:admin')
            ->namespace('Auth\\V1')
            ->group(function () {
                Route::post('login/account', 'AccessController@login');
                Route::post('login/captcha', 'AuthV1Controller@captcha');
            });
    });


