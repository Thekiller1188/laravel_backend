<?php

namespace App\Http\Controllers\api\courses;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    public function index() {
        // Eager load the 'category' relationship and count the 'videos' relationship
        $courses = Course::with('categories')->withCount('videos')->get();
    
        // Transform the data to include only necessary fields
        $courses = $courses->map(function ($course) {
            return [
                'id' => $course->id,
                'imgBG' => $course->imgBG,
                'prix' => $course->prix,
                'name' => $course->name,
                'desc' => $course->desc,
                'duration' => $course->duration,
                'category_id' => $course->categories->id,
                'category_name' => $course->categories->name,
                'videos_count' => $course->videos_count, // Include the count of videos
                // Include any other fields you need from the course
            ];
        });
    
        return response()->json($courses);
    }
    
    public function indexId(Request $request,$id){
        $courses = Course::find($id);
        $courses->numbervideo = $courses->videos()->count();
        return response()->json([
            $courses,],200);
    }

    public function search($name)
    {
        // Validate the name parameter
        if (empty($name) || !is_string($name)) {
            return response()->json(['error' => 'Invalid search query'], 422);
        }

        // Search in the 'name' and 'description' columns
        $results = Course::where('name', 'LIKE', "%{$name}%")
            ->orWhere('desc', 'LIKE', "%{$name}%")
            ->get();

        return response()->json($results);
    }
    public function coursesvideos(Request $request , $id){
    if($request->user()->role =='admin'){
        
        $videos = Video::where('course_id', $id)->get();
        return response()->json($videos);
    }    
}

    public function picindex( $name){
        return response()->file(resource_path('images/'.$name));

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
                'imgBG' => 'required|mimes:png,jpeg', // Adjust max file size as per your requirement
                'desc' => 'required|string',
                'prix'=> 'required|integer',
                'category_id'=> 'required|integer',
            ]);
            if($validator->fails())
            {
                return response()->json([
                    'status'=> 400,
                    'message'=> $validator->errors()->first(),
                ]);
            }
        
                $imageName = uniqid() . '_' . time() . '.' . $request->imgBG->getClientOriginalExtension();
                $request->imgBG->move(resource_path('images'), $imageName);
        
                $video = new Course();
                $video->name = $request->name;
                $video->imgBG = $imageName;
                $video->category_id = $request->category_id;
                $video->prix = $request->prix;
                $video->desc = $request->desc;
                $video->duration = 0;
                $video->save();
        
                return response()->json(['message' => $video], 200);
        }
    }
    public function remove(Request $request, $id)
    {
        if($request->user()->role == "user") {
            return response()->json([
                "status" => 400,
                "message" => "You do not have the permission to do that"
            ]);
        } else if($request->user()->role == "admin") {
            $course = Course::find($id);

            if($course) {
                // Delete associated videos from storage and database
                $videos = $course->videos;

                foreach ($videos as $video) {
                    // Delete the video file from storage
                    $videoPath = resource_path('videos/' . $video->path);
                    if (file_exists($videoPath)) {
                        unlink($videoPath);
                    }

                    // Delete the video record from the database
                    $video->delete();
                }

                // Delete the course
                $imagepath = resource_path('images/' . $course->imgBG);
                if($course->delete())
                {
                    unlink($imagepath);
                    return response()->json(['message' => 'The course was deleted successfully.'], 200);
                }

                
            } else {
                return response()->json(['message' => 'There was a problem deleting the course. Please retry later.'], 404);
            }
        }
    }
    
    

    public function update(Request $request, $id)
    {
        if($request->user()->role=="user")
        {
            return response()->json([
                "status"=> 400,
                "message"=>"you do not have the permission to do that" 
            ]);
        }
        else if($request->user()->role== "admin")
        {
            if(Course::find($id)){
                // Define validation rules based on the fields that can be updated
                $rules = [
                'name' => 'sometimes|string',
                'imgBG' => 'sometimes|mimes:png,jpeg',
                'desc' => 'sometimes|string',
                'prix'=> 'sometimes|integer',
                'duration'=> 'sometimes|integer',
                'category_id'=> 'sometimes|integer',
                ];
        
                // Validate the incoming request data
                $validator = Validator::make($request->all(), $rules);
        
                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
        
                // Find the course by ID
                $course = Course::findOrFail($id);
                $image = $course->imgBG; 
        
                // Update the user with only the validated fields
                $course->fill($validator->validated());
                if($request->has('imgBG'))
                {
                    $imageName = uniqid() . '_' . time() . '.' . $request->imgBG->getClientOriginalExtension();
                    $request->imgBG->move(resource_path('images'), $imageName);
                    
                    unlink(resource_path('images') .'/'. $image);
                    $course->imgBG = $imageName;
                }
        
                $course->save();
                
        
                // Return a response, usually the updated resource or a success message
                return response()->json([
                    'message' => 'Course updated successfully',
                    'course' => $course
                ], 200);
            }
            else{
                return response()->json([
                    "error"=>"didn't find this Course" 
                ],404);
            }
        }
    }



    public function getCoursesByCategory($categoryId)
{
    try {
        // Check if the category exists
        $category = Category::findOrFail($categoryId);

        // Get all courses in the specified category
        $coursesInCategory = Course::where('category_id', $categoryId)->get();

        return response()->json(['courses' => $coursesInCategory], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Category not found or an error occurred', 'details' => $e->getMessage()], 404);
    }
}

}
