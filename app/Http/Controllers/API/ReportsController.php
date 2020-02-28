<?php

namespace App\Http\Controllers\API;

use App\DR_ITEM;
use App\Http\Controllers\Controller;
use App\Models\Billing;
use DB;

class ReportsController extends Controller
{
    public function getSalesCost()
    {

        $billing_data = Billing::select(
            DB::raw('SUM(item_cost) as cost, SUM(amount) as gross_sales')
        )->join('items', 'items.id', '=', 'billings.item_id')->where('isReturned', 0)->first()->toArray();

        $data = DR_ITEM::select(
            DB::raw('SUM(item_cost) as cost, SUM(unit_price) as gross_sales')
        )->join('items', 'items.id', '=', 'transaction_items.item_id')->first()->toArray();

		$db_p = false; 
		
		if(!isset($data['cost'])) {
			$data['cost'] = 0;
			$db_p = true;
		}
		
		if(!$data['gross_sales']) {
			$data['gross_sales'] = 0;
			$db_p = true;
		}
		
        $data['cost'] += isset($billing_data['cost']) ? $billing_data['cost'] : 0 ;
        $data['gross_sales'] += isset($billing_data['gross_sales']) ? $billing_data['gross_sales'] : 0;

        $data['net_sales'] = $data['gross_sales'] - $data['cost'];
		
		
        $data['increase'] = $db_p ? 0 : ((100 + (($data['net_sales'] / $data['cost']) * 100)) / 100);

        return response([
            'success' => true,
            'data' => $data,
        ]);

    }
}
