<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\DR;
use App\DR_ITEM as Item;
use DB;
use Log;

class deliveryController extends Controller
{
    public function saveDelivery(Request $request)
    {
        $validate_data = \Validator::make($request->all(), [
            'dr_no' => 'required|unique:deliveryreciepts',
            'deliverd_to' => 'required',
            'address' => 'required',
            'd_style' => 'required',
            'datetime' => 'required',
            'terms' => 'required',
            'tin' => 'required',
            'updated_by' => 'required', //|exists:users,name
            'dr_list' => 'required',
            'dr_list.*.Item' => 'required',
            'dr_list.*.UnitPrice' => 'required|numeric',
            'dr_list.*.Quantity' => 'required|numeric',
            'dr_list.*.UnitID' => 'required',
        ], [
            'dr_no.unique' => 'Delivery Number has already taken',
            'dr_list.required' => 'Delivery List must have items',
            'dr_no.required' => 'Delivery Number was required',
            'address.required' => 'Address Field was required',
            'deliverd_to.required' => 'Recipient (Delivered To) was Required',
            'd_style.required' => 'Delivery Style was required',
            'dr_list.*.Item.required' => 'Delivery Item Name was Required',
            'dr_list.*.UnitPrice.required' => 'Delivery Item Unit Price was Required',
            'dr_list.*.Quantity.required' => 'Delivery Item Quantity was Required',
            'dr_list.*.UnitID.required' => 'Delivery Item Unit was Required',
        ]);


        if ($validate_data->fails()) {
            return response([
                'success' => false,
                'data' => $validate_data->errors(),
            ]);
        }


        DB::beginTransaction();

        try {

            $id = DR::create([
                'dr_no' => $request->dr_no,
                'delivered_to' => $request->deliverd_to,
                'address' => $request->address,
                'delivery_style' => $request->d_style,
                'date' => $request->datetime,
                'terms' => $request->terms,
                'tin' => $request->tin,
                'updated_by' => $request->updated_by,
            ]);

            foreach ($request->dr_list as $key => $value) {
                Item::create([
                    'transaction_id' => $id->id,
                    'quantity' => $value['Quantity'],
                    'unit' => $value['UnitID'],
                    'item' => $value['Item'],
                    'unit_price' => $value['UnitPrice'],
                ]);
            }
        } catch (\ValidationException $validation_ex) {
            DB::rollback();
            return $this->error_response($validation_ex);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error_response($e);
        }

        DB::commit();
        return response([
            'success' => true,
            'data' => 'Success! Delivery Reciept saved to our database.'
        ]);
    }

    protected function error_response($exception)
    {
        Log::critical($exception);
        return response([
            'success' => false,
            'data' => [
                'Server_Response' => [
                    '[500] : => The Server was experiencing an error, Please Contact the Developer',
                ],
            ],
        ]);
    }
}
