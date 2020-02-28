<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


use App\DR;
use App\DR_ITEM as Item;
use DB;
use Log;
use App\Models\Sales;
use App\Models\BankTransaction as BT;

class salesController extends Controller
{
    public function saveDelivery(Request $request)
    {
        $validate_data = \Validator::make($request->all(), [

            //primary transaction validation
            'transact_items.trans_no' => 'required|unique:sales,transaction_no',
            'transact_items.sold_to' => 'required',
            'transact_items.address' => 'required',
            'transact_items.b_style' => 'required',
            'transact_items.datetime' => 'required',
            'transact_items.terms' => 'required',
            'transact_items.po' => 'required',
            'transact_items.updated_by' => 'required|exists:users,id',
            'transact_items.item_list' => 'required',
            'transact_items.item_list.*.Item' => 'required',
            'transact_items.item_list.*.UnitPrice' => 'required|numeric',
            'transact_items.item_list.*.Quantity' => 'required|numeric',
            'transact_items.item_list.*.ItemID' => 'required|numeric|exists:items,id',

            'amount_received' => 'required',
            'total_amount' => 'required',

            //Cheque validation
            'cheque.*.BankAccountNumber' => 'required_unless:check_empty,false|numeric',
            'cheque.*.BankName' => 'required_unless:check_empty,false',
            'cheque.*.BankBranch' => 'required_unless:check_empty,false',
            'cheque.*.ChequeNumber' => 'required_unless:check_empty,false|numeric|unique:bank_transactions,cheque_number',
            'cheque.*.ChequeDate' => 'required_unless:check_empty,false',
            'cheque.*.ChequeAmount' => 'required_unless:check_empty,false|numeric',
            'cheque.*.IsPostDatedCheque' => 'required_unless:check_empty,false|boolean',

        ], [
            'transact_items.trans_no.unique' => 'Transaction Number has already taken',
            'transact_items.item_list.required' => 'Transaction List must have items',
            'transact_items.transact_items.trans_no.required' => 'Transaction Number was required',
            'transact_items.address.required' => 'Address Field was required',
            'transact_items.sold_to.required' => 'Recipient (Sold To) was Required',
            'transact_items.b_style.required' => 'Bus Style was required',
            'transact_items.po.required' => 'P.O. NO. was required',
            'transact_items.item_list.*.Item.required' => 'Item Name was Required',
            'transact_items.item_list.*.UnitPrice.required' => 'Item Unit Price was Required',
            'transact_items.item_list.*.Quantity.required' => 'Item Quantity was Required',
            'transact_items.item_list.*.UnitID.required' => 'Item Unit was Required',
            'transact_items.item_list.*.UnitID.exists' => 'Unit was not existing in the database',

            'amount_received.required' => 'Amount Received was required',
            'total_amount.required' => 'Total Amount was required [Wait? Are you doing some hacks?]',

            //Cheque validation
            'cheque.*.BankAccountNumber.required_unless' => 'Bank Account Number was Required',
            'cheque.*.BankName.required_unless' => 'Bank Name was Required',
            'cheque.*.BankBranch.required_unless' => 'Bank Branch was Required',
            'cheque.*.ChequeNumber.required_unless' => 'Bank Check Number was Required',
            'cheque.*.ChequeDate.required_unless' => 'Bank Check Date was Required',
            'cheque.*.ChequeAmount.required_unless' => 'Bank Check Amount was Required',
            'cheque.*.IsPostDatedCheque.required_unless' => 'Bank Post Dated Checkbox was Required [Doing some hacks again?]',

            'cheque.*.BankAccountNumber.numeric' => 'Bank Account Number must be a numeric',
            'cheque.*.ChequeNumber.numeric' => 'Bank Check Number must be a numeric',
            'cheque.*.ChequeAmount.numeric' => 'Bank Check Amount must be a numeric',
            'cheque.*.IsPostDatedCheque.boolean' => 'Bank Post Dated must be checked [Doing some hacks again?]',


            'cheque.*.ChequeNumber.unique' => 'Bank Check Number was already been used',

        ]);


        if ($validate_data->fails()) {
            return response([
                'success' => false,
                'data' => $validate_data->errors(),
            ]);
        }


        DB::beginTransaction();

        try {

            $transact_items = (object) $request->transact_items;
            $cheque = (object) $request->cheque;
            $id = Sales::create([
                'transaction_no' => $transact_items->trans_no,
                'sold_to' => $transact_items->sold_to,
                'address' => $transact_items->address,
                'bus_style' => $transact_items->b_style,
                'date' => $transact_items->datetime,
                'terms' => $transact_items->terms,
                'po_no' => $transact_items->po,
                'updated_by' => $transact_items->updated_by,
                'total_amount' => $request->total_amount,
                'amount_paid' => $request->amount_received,
            ]);

            foreach ($transact_items->item_list as $key => $value) {
                Item::create([
                    'transaction_id' => $id->id,
                    'quantity' => $value['Quantity'],
                    'item_id' => $value['ItemID'],
                    'unit_price' => $value['UnitPrice'],
                ]);
            }

            foreach ($cheque as $key => $value) {
                $bt_ = BT::create([
                    'bank_account_number' => $value['BankAccountNumber'],
                    'bank_name' => $value['BankName'],
                    'bank_branch' => $value['BankBranch'],
                    'cheque_number' => $value['ChequeNumber'],
                    'cheque_date' => $value['ChequeDate'],
                    'cheque_amount' => $value['ChequeAmount'],
                    'post_dated_check' => $value['IsPostDatedCheque']
                ]);

                $id->bank_trans_id = $bt_->id;
                $id->save();
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
            'data' => 'Success! Sales Invoice saved to our database.'
        ]);
    }

    public function generate_rpt_sales()
    {
        $rpts = Sales::select('id', 'transaction_no', 'sold_to', 'date', 'total_amount', 'amount_paid', 'bank_trans_id')->with('items', 'bank_transaction')->get()->toArray();


        //generating total cost
        $data_item_id = collect($rpts)->map(function ($v, $k) {
            return collect($v['items'])->map(function ($vv, $kk) {
                return $vv['item_id'];
            });
        });

        collect($data_item_id)->map(function ($v, $k) use (&$rpts) {
            $rpts[$k]['SALES'] = collect($rpts[$k]['items'])->sum('unit_price');
            $rpts[$k]['COST'] = collect(\App\Models\Item::select('id', 'item_cost')->whereIn('id', $v)->get()->toArray())->sum('item_cost');
        });

        return response([
            'success' => true,
            'data' => $rpts
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
