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
            CustomerItem::select('items.id as item_id', 'items.item_name', 'items.item_size', 'customer_items.selling_price', 'customer_items.id', 'customer_items.discount')
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

            $exist = CustomerItem::where([
                ['customer_id', $request->customer_id],
                ['item_id', $request->item_id],
            ])->first();

            if ($exist) {
                return response([
                    'success' => false,
                    'data' => [
                        'Server_Response' => [
                            'You already bind this item',
                        ],
                    ],
                ]);
            }

            CustomerItem::create([
                'selling_price' => $request->selling_price,
                'discount' => $request->discount,
                'customer_id' => $request->customer_id,
                'item_id' => $request->item_id,
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

    public function editBindItem(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            "bindID" => 'required|exists:customer_items,id',
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

            \Log::info($request);
            $binded_item = CustomerItem::find($request->bindID);
            $binded_item->selling_price = $request->selling_price;
            $binded_item->discount = $request->discount;
            $binded_item->save();

            \Log::info($binded_item);
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
            'data' => 'Success!, Binded Item was successfully Updated',
        ]);
    }

    public function deleteItem(Request $request, $id)
    {
        $item_ = CustomerItem::find($id);

        if (!$item_) {
            return response([
                'success' => false,
                'data' => [
                    'Server_Response' => [
                        '[404] : => Item was not existing',
                    ],
                ],
            ]);
        }

        $item_->delete();

        return response([
            'success' => true,
            'data' => 'Item was successfully Unbound to the customer items.',
        ]);
    }
}
