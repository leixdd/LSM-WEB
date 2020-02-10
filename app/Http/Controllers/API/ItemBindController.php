<?php

namespace App\Http\Controllers\API;

use App\CustomerItem;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Log;

class ItemBindController extends Controller
{
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

    public function getAllBindItems($id)
    {
        return response([
            'success' => true,
            'data' =>
            CustomerItem::select('items.item_name', 'items.item_size', 'customer_items.selling_price', 'customer_items.id', 'customer_items.discount')
                ->join('items', 'items.id', '=', 'customer_items.item_id')
                ->where('customer_id', $id)->get()->toJSON(),
        ]);
    }
    

    public function bindItemToCustomer(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            "item_id" => 'required|exists:items,id',
            "customer_id" => 'required|exists:customers,id',
            "selling_price" => 'required|numeric',
            'discount' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response([
                'success' => false,
                'data' => $validator->errors(),
            ]);
        }

        DB::beginTransaction();

        try {

            CustomerItem::create([
                'customer_id' => $request->customer_id,
                'item_id' => $request->item_id,
                'selling_price' => $request->selling_price,
                'discount' => $request->discount,
            ]);

        } catch (\ValidationException $e) {
            DB::rollback();
            return $this->error_response($e);
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error_response($e);
        }

        DB::commit();

        return response([
            'success' => true,
            'data' => 'Success!, Item was successfully Bind to Customer',
        ]);

    }
}
