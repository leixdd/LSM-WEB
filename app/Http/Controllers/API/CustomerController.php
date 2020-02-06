<?php

namespace App\Http\Controllers\API;

use App\Customer;
use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;
use Log;

class CustomerController extends Controller
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

    public function addCustomer(Request $request)
    {

        $validator = \Validator::make($request->all(), [
            'Name' => 'required',
            'CompanyName' => 'required',
            'ContactNumber' => 'required',
            'Email' => 'required|email',
            'CompanyAddress' => 'required',
        ]);

        if ($validator->fails()) {

            return response([
                'success' => false,
                'data' => $validator->errors(),
            ]);
        }

        DB::beginTransaction();

        try {

            Customer::create([
                'customer_name' => $request->Name,
                'company_name' => $request->CompanyName,
                'contact_number' => $request->ContactNumber,
                'email' => $request->Email,
                'company_address' => $request->CompanyAddress,
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
            'data' => 'Success!, Customer ' . $request->Name . ' was saved into our database',
        ]);

    }

    public function listCustomers()
    {
        return response([
            'success' => true,
            'data' =>  Customer::get()->toJSON()
        ]);
    }
}
