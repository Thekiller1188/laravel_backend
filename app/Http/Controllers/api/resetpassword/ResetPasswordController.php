<?php

namespace App\Http\Controllers\api\resetpassword;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ResetPasswordController extends Controller
{
    public function reset(Request $request)
    {
        $rules = [
            'token' => 'required',
            'password' => 'required|min:8',
        ];

        // Validate the incoming request data
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $tokenable = DB::table('password_reset_tokens')->where('token', $request->token);
        $emailcheck = $tokenable->first();
        if (!$emailcheck) 
        {
            return response()->json(['errors'=> 'theres no reset token for this email'],404);
        }
        else
        {
        $user = User::where('email', $emailcheck->email)->first();
        if( Hash::check($request->password, $user->password)) {
            return response()->json([
              'message'=>'password is the same as the last one pls change to a different one'
            ],400);
        }
        else
        {
            DB::beginTransaction();
        $user->password = bcrypt($request->password);
        if($user->save())
        {
            $tokenable->delete();
            DB::commit();
            return response()->json([
               'message'=>'password changed successfully'
            ],200);
        }
        else
        {
            DB::rollBack();
            return response()->json([
                'message'=>'something wrong happened'
             ],400);
        }

        }
    }

    }
}
