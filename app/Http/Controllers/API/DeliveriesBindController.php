<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Billing;
use App\DR;
use App\Models\BankTransaction;

class DeliveriesBindController extends Controller
{
    public function getDRs($id) {
        return response([
            'success' => true,
            'data' => DR::with('bank_transactions','billing_items')->where('delivered_to', $id)->get()
        ]);
    }
}
