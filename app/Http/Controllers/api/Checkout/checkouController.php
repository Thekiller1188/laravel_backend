<?php

namespace App\Http\Controllers\api\checkout;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class checkouController extends Controller
{
    public function checkou(Request $request)
    {
        return response()->json([
            'message'=>'it works'
        ]);   
    }
}
