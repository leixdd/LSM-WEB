<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Billing;
use App\DR;
use App\Models\BankTransaction;
use App\Models\Item;

class DeliveriesBindController extends Controller
{
    public function getDRs($id) {
        $models = DR::with('bank_transactions','billing_items', 'user', 'customer')->where('delivered_to', $id)->get()->toArray();

        for ($i=0; $i < count($models); $i++) { 
            $billing_items = $models[$i]['billing_items'];
            for($j=0; $j < count($billing_items); $j++) {
                $models[$i]['billing_items'][$j]['item'] = Item::find($billing_items[$j]['item_id']);
            }
        }

        return response([
            'success' => true,
            'data' => $models
        ]);

    }
}
