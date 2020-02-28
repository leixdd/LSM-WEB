<?php

namespace App\Http\Controllers\API;

use App\DR;
use App\Http\Controllers\Controller;
use App\Models\Billing;
use App\Models\Item;
use DB;
use Illuminate\Http\Request;
use Log;

class DeliveriesBindController extends Controller
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

    public function getDRs($id)
    {
        $models = DR::with('bank_transactions', 'billing_items', 'user', 'customer')->where('delivered_to', $id)->get()->toArray();
        for ($i = 0; $i < count($models); $i++) {
            $billing_items = $models[$i]['billing_items'];
            $models[$i]['total_amount'] = Billing::where([
                ['dr_trans_no', $models[$i]["id"]],
                ['isReturned', 0],
            ])->sum('amount');
            for ($j = 0; $j < count($billing_items); $j++) {
                $models[$i]['billing_items'][$j]['item'] = Item::find($billing_items[$j]['item_id']);
            }
        }

        return response([
            'success' => true,
            'data' => $models,
        ]);

    }

    public function setToReturn(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            "bindID" => 'required|exists:billings,id',
            "rtStatus" => 'required',
        ]);

        if ($validator->fails()) {
            return response([
                'success' => false,
                'data' => $validator->errors(),
            ]);
        }

        DB::beginTransaction();

        try {
            $item = Billing::find($request->bindID);
            $item->isReturned = $request->rtStatus;
            $item->save();
        } catch (\Exception $e) {
            DB::rollback();
            return $this->error_response($e);
        }

        DB::commit();
        return response([
            'success' => true,
            'data' => 'Success!, Item was set to returned items',
        ]);
    }
}
