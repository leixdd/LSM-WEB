<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\DR;
use App\Models\Billing;
use App\Models\BankTransaction as BT;
use DB;
use Log;


class deliveryController extends Controller
{
    public function saveDelivery(Request $request)
    {
        \Log::error($request->all());
        $validate_data = \Validator::make($request->all(), [
            'dr_no' => 'required|unique:deliveryreciepts',
            'deliverd_to' => 'required|exists:customers,id',
            'address' => 'required',
            'd_style' => 'required',
            'datetime' => 'required|date',
            'datetime_to_be_paid' => 'required|date',
            'terms' => 'required',
            'tin' => 'required',
            'updated_by' => 'required|exists:users,id', //|exists:users,name

            'dr_list' => 'required',
            'dr_list.*.Item' => 'required',
            'dr_list.*.UnitPrice' => 'required|numeric',
            'dr_list.*.Quantity' => 'required|numeric',
            'dr_list.*.ItemID' => 'required|numeric|exists:items,id',

            'amount_received' => 'required_unless:check_empty,false|numeric',
            'total_amount' => 'required|numeric',

            //Cheque validation
            'cheque.*.BankAccountNumber' => 'required_unless:check_empty,false|numeric',
            'cheque.*.BankName' => 'required_unless:check_empty,false',
            'cheque.*.BankBranch' => 'required_unless:check_empty,false',
            'cheque.*.ChequeNumber' => 'required_unless:check_empty,false|numeric|unique:bank_transactions,cheque_number',
            'cheque.*.ChequeDate' => 'required_unless:check_empty,false',
            'cheque.*.ChequeAmount' => 'required_unless:check_empty,false|numeric',
            'cheque.*.IsPostDatedCheque' => 'required_unless:check_empty,false|boolean',

        ], [

            'dr_no.unique' => 'Delivery Number has already taken',
            'dr_list.required' => 'Delivery List must have items',
            'dr_no.required' => 'Delivery Number was required',
            'address.required' => 'Address Field was required',
            'deliverd_to.required' => 'Recipient (Delivered To) was Required',
            'd_style.required' => 'Delivery Style was required',
            'dr_list.*.Item.required' => 'Delivery Item Name was Required',
            'dr_list.*.UnitPrice.required' => 'Delivery Item Unit Price was Required',
            'dr_list.*.Quantity.required' => 'Delivery Item Quantity was Required',
            'dr_list.*.ItemID.exists' => 'Delivery Item was not existing',

            'amount_received.required' => 'Amount Received was required',
            'total_amount.required' => 'Total Amount was required [Wait? Are you doing some hacks?]',

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

            $id = DR::create([
                'dr_no' => $request->dr_no,
                'delivered_to' => $request->deliverd_to,
                'address' => $request->address,
                'delivery_style' => $request->d_style,
                'date' => $request->datetime,
                'date_to_be_paid' => $request->datetime_to_be_paid,
                'terms' => $request->terms,
                'tin' => $request->tin,
                'updated_by' => $request->updated_by,
                'total_amount' => $request->total_amount,
                'amount_received' => $request->amount_received
            ]);

            foreach ($request->dr_list as $key => $value) {
                Billing::create([
                    'dr_trans_no' => $id->id,
                    'quantity' => $value['Quantity'],
                    'item_id' => $value['ItemID'],
                    'amount' => $value['UnitPrice'],
                ]);
            }

            if(!$request->check_empty) {
                $cheque = (object) $request->cheque;
            
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
    
                    $id->bank_transactions()->attach([$bt_->id]);
                }
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
            'data' => 'Success! Delivery Reciept saved to our database.',
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
