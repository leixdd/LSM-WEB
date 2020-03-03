<?php

namespace App\Http\Controllers\API;

use App\DR_ITEM;
use App\Http\Controllers\Controller;
use App\Models\Billing;
use DB;
use App\DR;

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

    public function getDueDates() {
        //getting all dr that was unpaid or inComplete 
        $dates = DR::select('deliveryreciepts.date_to_be_paid', 'deliveryreciepts.dr_no', 'customers.customer_name', 'deliveryreciepts.amount_received', 'deliveryreciepts.total_amount' , \DB::raw('DATEDIFF(deliveryreciepts.date_to_be_paid, deliveryreciepts.date) as remaining_days'))->join('customers', 'customers.id', '=', 'deliveryreciepts.delivered_to')->where([
            [DB::raw('DATEDIFF(deliveryreciepts.date_to_be_paid, deliveryreciepts.date)'), '<=', 90],
            [DB::raw('(deliveryreciepts.total_amount - deliveryreciepts.amount_received)'), '>', 0]
        ])->get()->toArray();

        return response([
            'success' => true,
            'data' => $dates
        ]);
    }
}
