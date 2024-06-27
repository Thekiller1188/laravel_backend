<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\Purchase;
use App\Models\User;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class testcontroller extends Controller
{
    public function index(Request $request){
        if($request->user()->role=="user"){
            return response()->json([
                "status"=> 400,
                "message"=>"you do not have the permission to do that" 
            ]);
        }
        else if($request->user()->role== "admin"){
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
            ]);
            if($validator->fails())
            {
                return response()->json([
                    'status'=> 400,
                    'message'=> $validator->errors()->first(),
                ]);
            }

            $cat = Category::findOrFail(1);
            $cat->name= $request->name;
            $cat->update();
            return response()->json([
                'message' => 'Formation updated successfully',
                'formation' => $cat
            ], 200);
        }
    }
    public function role(){
        $user = new Course();
        $user->name = 'ewqeqwe';
        $user->prix = 12;
        $user->desc = 'eqweqwe';
        $user->imgBG = 'weqweq';
        $user->duration = 34;
        $user->category_id = 1;
        $user->save();
    }
}
