<?php

namespace App\Http\Controllers\api\resetpassword;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), ['email' => "required|email|exists:users",]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            else{
        $token = Str::random(64);
        if($emailexist = DB::table('password_reset_tokens')->where('email', $request->email)->delete()){
        }

        DB::table('password_reset_tokens')->insert([
            'email'=> $request->email,
            'token'=> $token,
            'created_at'=> Carbon::now(),
        ]);

        
        if(Mail::send("emails.index",['token'=>$token],function ($message) use ($request) {
            $message->to($request->email)->subject('ResetPassword');
        }))
        {return response()->json([
            'message'=>'the email has been sent',
            'email'=> $request->email,
        ],200);
    }
    else{
        return response()->json([
            'message'=>'error in sending the mail',
        ],400);
    }
}
    }
}
