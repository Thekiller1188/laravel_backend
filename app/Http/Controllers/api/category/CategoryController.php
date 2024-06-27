<?php

namespace App\Http\Controllers\api\category;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }
    public function indexId($id)
{
    $category = Category::find($id);

    if ($category) {
        // Fetch the courses for the given category


        return response()->json($category);
    } else {
        return response()->json(['error' => 'Category not found'], 404);
    }
}

    public function add(Request $request)
    {
        if($request->user()->role=="user")
        {
            return response()->json([
                "message"=>"you do not have the permission to do that" 
            ],401);
        }
        else if($request->user()->role== "admin")
        {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'description'=>'required|string',
                'image'=>'required|mimes:png,jpg,jpeg|max:7400'
            ]);
            if($validator->fails())
            {
                return response()->json([
                    'message'=> $validator->errors()->first(),
                ],422);
            }
            $category = new Category();
            $category->name = $request->name;
            $category->description = $request->description;
            $imagename  = uniqid() . '_' . time() . '.' . $request->image->getClientOriginalExtension();
               if($request->image->move(resource_path('images'), $imagename) )
               {
                $category->image = $imagename;
               }
               else{
                return response()->json([
                    'error'=>'there was a problem'
                    ],400);
               }
            $category->save();

            return response()->json([
                'status'=> 200,
                'message'=> 'added a category with success',
                'category' => $category 

            ],200);
        }
    }
    public function update(Request $request, $id)
    {
        if($request->user()->role== 'user')
        {
            return response()->json([
                "status"=> 400,
                "message"=>"you do not have the permission to do that" 
            ]);
        }
        else if($request->user()->role== 'admin'){
            $category = Category::find($id);
            if($category->id ==1)
            {
                return response()->json([
                    "message"=> "this category is not updatble"
                    ],400);
            }
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string',
                'description'=>'sometimes|string',
                'image'=> 'sometimes|mimes:png,jpg,jpeg|max:7400',
            ]);
            if($validator->fails())
            {
                return response()->json([
                    'status'=> 400,
                    'message'=> $validator->errors()->first(),
                ]);
            }
            if($request->has('name')){$category->name = $request->name;}
            if($request->has('description')){$category->description = $request->description;}
            if($request->has('image')){
               $imagename  = uniqid() . '_' . time() . '.' . $request->image->getClientOriginalExtension();
               if($request->image->move(resource_path('images'), $imagename) )
               {
                $category->image = $imagename;
               }
               else{
                return response()->json([
                    'error'=>'there was a problem'
                    ],400);
               }
            }
            
            
            

            $category->save();

            return response()->json([ 
                'message'=> 'modified a category with success',
                'category' => $category 

            ],200);
        }
    }



    public function destroy(Request $request,$id)
    {
        if($request->user()->role== 'user')
        {
            return response()->json([
                "status"=> 400,
                "message"=>"you do not have the permission to do that" 
            ]);
        }
        else if( $request->user()->role== "admin")
        {
            $category = Category::find($id);
            $id = $category->id;
            if($category->id ==1)
            {
                return response()->json([
                    "message"=> "this category is not deletable"
                    ],400);
            }
            else
            {
                $courses = Course::where("category_id", $id)->get();
                foreach($courses as $course)    
                {
                    $course->category_id = 1;
                }
            $category->delete();
            }
        }
    }
}
