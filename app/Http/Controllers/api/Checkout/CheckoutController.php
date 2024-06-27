<?php

namespace App\Http\Controllers\api\Checkout;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Purchase;
use App\Models\Purchaseitem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CheckoutController extends Controller
{
    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        "course_ids" => "required|array",
    ]);

    if ($validator->fails()) {
        return response()->json([
            "error" => $validator->errors(),
        ], 422);
    }



    $user = $request->user();
    $courseIds = $request->input('course_ids'); // Array of course IDs

    DB::beginTransaction();

    try {
        $newCourses = [];
        foreach ($courseIds as $courseId) {
            $course = Course::findOrFail($courseId);

            $alreadyPurchased = Purchaseitem::where('course_id', $course->id)
                ->whereHas('purchases', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->exists();

            if (!$alreadyPurchased) {
                $newCourses[] = $courseId;
            }
        }

        if (empty($newCourses)) {
            // If all courses are already owned, return a message without creating a new purchase
            DB::rollBack();
            return response()->json([
                'error' => 'All courses are already owned',
        ], 404);
        }

        // Create a new purchase
        $purchase = new Purchase();
        $purchase->user_id = $user->id;
        $purchase->save();

        foreach ($newCourses as $courseId) {
            $purchaseItem = new Purchaseitem();
            $purchaseItem->purchase_id = $purchase->id;
            $purchaseItem->course_id = $courseId;
            $purchaseItem->save();
        }

        DB::commit();

        return response()->json(['message' => 'Purchase completed successfully'], 200);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => 'Purchase failed', 'details' => $e->getMessage()], 500);
    }
}



}
