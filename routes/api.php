<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// RESTful API version 1.
Route::group(['middleware'=>\Barryvdh\Cors\HandleCors::class], function () {
    require base_path('routes/api_v1.php');
});