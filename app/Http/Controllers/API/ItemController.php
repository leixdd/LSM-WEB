<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Log;
use App\Models\Item;

class ItemController extends Controller
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

    public function save(Request $request) {
        $vd = \Validator::make($request->all(), [
            'item_name' => 'required',
            'item_cost' => 'required|numeric', 
            'item_size' => 'required'
        ], [
            'item_name.required' => 'Item Name must have a value',
            'item_cost.required' => 'Unit Price must have a value', 
            'item_unit.required' => 'Unit must have a value'
        ]);

        if($vd->fails()) {
            return response([
                'success' => false,
                'data' => $vd->errors()
            ]);
        }

        DB::beginTransaction();

        try {

            $exist = Item::where([
                'item_name' => $request->item_name,
                'item_size' => $request->item_size,
            ])->count() > 0;
            

            if(!$exist) {
                Item::create([
                    'item_name' => $request->item_name,
                    'item_size' => $request->item_size,
                    'item_cost' => $request->item_cost
                ]);
            }else {
                return response([
                    'success' => false,
                    'data' => [
                        'Server_Response' => [
                            'Item was already Existing',
                        ],
                    ],
                ]);
            }


        }catch(\ValidationException $e) {
            DB::rollback();
            return $this->error_response($e);
        }catch(\Exception $e) {
            DB::rollback();
            return $this->error_response($e);
        }

        DB::commit();

        return response([
            'success' => true,
            'data' => 'Success!, Item ' . $request->item_name . ' was saved into our database'
        ]);
    } 

    public function getItems() {
        $d = (object) Item::with('unit')->get();
        $def_v = [];
        
        foreach($d as $data) {
         $def_v[] = [
            'item_name' => $data->item_name,
            'item_cost' => $data->item_cost,
            'item_size' => $data->item_size,
            'item_id' => $data->id
         ];   
        }

        return response([
            'success' => true,
            'data' => json_encode($def_v)
        ]);
    }

    public function deleteItem($id) {
        $item_ = Item::find($id);

        if(!$item_) {
            return response([
                'success' => false,
                'data' => [
                    'Server_Response' => [
                        '[404] : => Item was not existing',
                    ],
                ]
            ]);
        }

        $item_->delete();

        return response([
            'success' => true,
            'data' => 'Item was successfully Deleted.'
        ]);

    }

}
