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

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
// Route::group(['middleware' => 'guest:api'], function () {
Route::post('login', 'Auth\LoginController@login')->name('login');
Route::post('register', 'Auth\RegisterController@register')->name('register');
// Route::post('check-availability', 'Auth\RegisterController@checkAvailability');
Route::post('verify', 'Auth\RegisterController@verifyUser')->name('verify');
// Route::post('password/email', 'Auth\ForgotPasswordController@sendResetPassword')->name('get-reset-token');
Route::post('password/reset', 'Auth\ResetPasswordController@recover')->name('reset');

Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.request');
// });

Route::group(['middleware' => ['jwt.auth']], function () {
    Route::get('logout', 'Auth\LoginController@logout');
    Route::get('test', function () {
        return response()->json(['foo' => 'bar']);
    });
});

Route::group(['middleware' => 'jwt.refresh'], function () {
    Route::get('refresh', [
        'middleware' => 'jwt.refresh',
        function () {
            return response()->json([
                "status" => 200,
                "data" => [],
                "response" => [
                    'message' => 'By accessing this endpoint, you can refresh your access token at each request. Check out this response headers!',
                ],
            ]);
        },
    ]);
});
