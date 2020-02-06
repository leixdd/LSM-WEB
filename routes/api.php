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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//TODO: Apply CORS

Route::group(['prefix' => 'account'], function () {
    Route::post('login', 'API\AuthController@login');
});

Route::group(['prefix' => 'dr'], function () {
    Route::post('save', 'API\deliveryController@saveDelivery');
});


Route::group(['prefix' => 'sales'], function () {
    Route::post('save', 'API\salesController@saveDelivery');
    Route::group(['prefix' => 'reports'], function () {
        Route::get('sales', 'API\salesController@generate_rpt_sales');
    });
});

Route::group([
    'prefix' => 'items'
], function() {
    Route::post('save', 'API\ItemController@save');
    Route::get('/', 'API\ItemController@getItems');
    Route::post('remove/{id}', 'API\ItemController@deleteItem');
});

Route::get('units', function () {
    try {
        return response([
            'success' => true,
            'data' => \App\Models\Unit::get()
        ]);
    } catch (\Exception $e) {
        return response([
            'success' => false,
            'data' => ""
        ]);
    }
});
