<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{

    public function login(Request $request)
    {

        $validated_data = \Validator::make($request->all(), [
            'txtUserID' => 'required|exists:users,name',
            'txtPass' => 'required'
        ], [
            'txtUserID.required' => 'Username was required',
            'txtUserID.exists' => 'Username was not existing in the database',
            'txtPass.required' => 'Password was required',
        ]);



        if ($validated_data->fails()) {
            return response([
                'success' => false,
                'data' => $validated_data->errors()
            ]);
        }

        $user = \App\User::where('name', $request->txtUserID)->first();

        if (!\Hash::check($request->txtPass, $user->password)) {
            $validated_data->errors()->add('txtPass', 'Wrong Credentials, Please try again');
            return response([
                'success' => false,
                'data' => $validated_data->errors()
            ]);
        } else {
            return response([
                'success' => true,
                'data' => $user->id
            ]);
        }

        return response([
            'success' => false,
            'data' => 'Server Error'
        ]);
    }
}
