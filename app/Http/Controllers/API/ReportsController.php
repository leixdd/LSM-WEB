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
        )->join('items', 'items.id', '=', 'billings.item_id')->first()->toArray();

        $data = DR_ITEM::select(
            DB::raw('SUM(item_cost) as cost, SUM(unit_price) as gross_sales')
        )->join('items', 'items.id', '=', 'transaction_items.item_id')->first()->toArray();

        $data['cost'] += $billing_data['cost'];
        $data['gross_sales'] += $billing_data['gross_sales'];

        $data['net_sales'] = $data['gross_sales'] - $data['cost'];
        $data['increase'] = ((100 + (($data['net_sales'] / $data['cost']) * 100)) / 100);

        return response([
            'success' => true,
            'data' => $data,
        ]);

    }
}
