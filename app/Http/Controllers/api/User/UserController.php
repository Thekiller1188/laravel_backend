<?php

namespace App\Http\Controllers\api\User;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function rolecheck(Request $request)
    {
        $userrole = $request->user()->role; 
        if ($userrole == "admin") {  
            return response()->json([   
                "role"=> $userrole
                ],200);
            }
            else{
                return response()->json([
                    "error"=> 'you are not authorized for this'
                    ],401);
            }
    }

    public function indexInvoice(Request $request)
    {
        $user = $request->user();

    if ($user->role == 'admin') {
        $purchases = Purchase::with(['users', 'items.courses'])->get();

        $invoices = $purchases->map(function($purchase) {
            $courses = $purchase->items->map(function($items) {
                return $items->courses->name;
            });
            $totalPrice = $purchase->items->sum(function($purchaseItem) {
                return $purchaseItem->courses->prix;
            });

            return [
                'user_name' => $purchase->users->name,
                'courses' => $courses,
                'total_price' => $totalPrice,
                'purchase_date' => $purchase->created_at->toDateString(),
            ];
        });

        return response()->json($invoices);
    }

    return response()->json(['message' => 'Unauthorized'], 403);
}
    
public function userInvoices(Request $request)
{
    $user = $request->user();

    // Retrieve purchases for the authenticated user
    $purchases = Purchase::with(['items.courses'])
        ->where('user_id', $user->id)
        ->get();

    // Map purchases to the desired invoice format
    $invoices = $purchases->map(function($purchase) {
        $courses = $purchase->items->map(function($item) {
            return $item->courses->name;
        });
        $totalPrice = $purchase->items->sum(function($purchaseItem) {
            return $purchaseItem->courses->prix;
        });

        return [
            'user_name' => $purchase->users->name,
            'courses' => $courses,
            'total_price' => $totalPrice,
            'purchase_date' => $purchase->created_at->toDateString(),
        ];
    });

    if ($invoices->isEmpty()) {
        return response()->json(['message' => 'No invoices found for this user.'], 404);
    }

    return response()->json($invoices, 200);
}
public function userindex(Request $request)
{
    $user = User::find($request->user()->id);

    if($user->role == 'admin')
    {
        $users = User::all();
        if(!empty($users))
        {return response()->json([$users],200);}
        else{return response()->json(['error'=>'no user found'],200);}
        
    }
}
    public function update(Request $request)
    {
        if($request->user()){
        // Define validation rules based on the fields that can be updated
        $rules = [
            'name' => 'sometimes|string|max:255',
            'lastname'=>'sometimes|string|max:255',
            'firstname'=>'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,',
            'username' => 'sometimes|string|max:255',
            'password' => 'required|string|min:8',
        ];

        // Validate the incoming request data
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Find the user by ID
        $user = User::find($request->user()->id);

        // Update the user with only the validated fields
        $user->fill($validator->validated());
        
        if (password_verify($request->password,$user->password)) {
            // If password is present, hash it before saving
            $user->save();
            return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ], 200);
        }
        else{
            return response()->json([
                'error' => 'password incorrect',
            ], 401);
        }

        

        // Return a response, usually the updated resource or a success message
        
    }
    }

    public function userCourses(Request $request)
    {
        try {
            $user = $request->user();

            // Eager load purchases and their associated items and courses
            $userCourses = $user->purchases()->with('items.courses')->get()->pluck('items')->flatten()->pluck('courses')->unique();

            if ($userCourses->isNotEmpty()) {
                return response()->json([
                    "courses" => $userCourses,
                ], 200);
            } else {
                return response()->json([
                    "message" => "You have no courses. Buy some courses.",
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                "error" => "Something went wrong.",
            ], 500);
        }
    }


    public function courseVideos(Request $request, $courseId)
    {
        try {
            $user = $request->user();

            // Check if the user has purchased the specified course
            $course = Course::findOrFail($courseId);
            $hasPurchased = $user->purchases()->with('items.courses')->get()->pluck('items')->flatten()->pluck('courses')->unique()->contains($course);

            if (!$hasPurchased) {
                return response()->json([
                    "error" => "User has not purchased this course.",
                ], 403);
            }

            // User has purchased the course, fetch its videos
            $courseVideos = $course->videos;

            if ($courseVideos->isNotEmpty()) {
                return response()->json([
                    "videos" => $courseVideos,
                ], 200);
            } else {
                return response()->json([
                    "message" => "No videos found for this course.",
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                "error" => "Something went wrong.",
            ], 500);
        }
    }
    
    public function userinf(Request $request)
    {
        if($request->user()){$user = User::findOrFail($request->user()->id);
        if($user)
        {
            
        return response()->json([
        "user"=> $user,
        ],200);
        }
        else{
            return response()->json([
                "error"=> "no user found",
                ],404);
        }}
        else{
            return response()->json([
                "error"=> "non authorized",
                ],401);
        }
        
    }

    public function updatepassword(Request $request)
    {
        $validate  = Validator::make($request->all(),[
            'oldPassword'=>'required|string|max:255',
            'newPassword'=>'required|string|max:255',
        ]);

        if($validate->fails())
        {
            return response()->json(['errors' => $validate->errors()], 422);
        }


        $user = User::find($request->user()->id);

        if($user)
        {
            if(password_verify($request->oldPassword,$user->password))
            {
                $user->password = bcrypt($request->newPassword);
                $user->save();
                return response()->json(['message' => 'password changed '], 200);
            }
            else{
                return response()->json(['error' => 'password incorect'], 404);
            }
        }
        else{
            return response()->json(['error' => 'user not found'], 404);
        }
    }
    
    
}
