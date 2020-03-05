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
        Route::get('ncg', 'API\ReportsController@getSalesCost');
        Route::get('notifications', 'API\ReportsController@getDueDates');
    });
});

Route::group([
    'prefix' => 'items',
], function () {
    Route::get('/', 'API\ItemController@getItems');
    Route::post('save', 'API\ItemController@save');
    Route::post('edit/{id}', 'API\ItemController@edit');
    Route::post('remove/{id}', 'API\ItemController@deleteItem');

});

Route::get('units', function () {
    try {
        return response([
            'success' => true,
            'data' => \App\Models\Unit::get(),
        ]);
    } catch (\Exception $e) {
        return response([
            'success' => false,
            'data' => "",
        ]);
    }
});

Route::group([
    'prefix' => 'customers',
], function () {
    Route::get('/', 'API\CustomerController@listCustomers');
    Route::post('/', 'API\CustomerController@addCustomer');
    Route::get('/bindItems/{id}', 'API\ItemBindController@getAllBindItems');
    Route::post('/editBindItem', 'API\ItemBindController@editBindItem');
    Route::post('removeBindItem/{id}', 'API\ItemBindController@deleteItem');
    Route::post('/bindItem', 'API\ItemBindController@bindItemToCustomer');

    //Deliveries
    Route::get('/deliveries/{id}', 'API\DeliveriesBindController@getDRs');

    Route::post('/setToReturn', 'API\DeliveriesBindController@setToReturn');
});


Route::get('/util', function () {
    $customers = \App\Customer::select('id')->whereNotIn('id', \App\CustomerItem::select('customer_id')->distinct()->get()->toArray())->get();
    $items = \App\Models\Item::select('id as item_id', 'item_cost as selling_price')->get()->toArray();

    \DB::beginTransaction();
    foreach ($customers as $customer) {
        try {
            foreach ($items as $item) {

                \App\CustomerItem::insertOrIgnore([
                    'customer_id' => $customer['id'],
                    'item_id' => $item['item_id'],
                    'selling_price' => $item['selling_price'],
                    'discount' => 0,
                ]);
            }
        } catch (\Exception $e) {
            \DB::rollback();
            return response(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    \DB::commit();

    return response(['success' => true]);
});
