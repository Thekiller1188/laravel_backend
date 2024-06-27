<?php

namespace App\Http\Controllers\api\Video;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use App\Models\Video;
use FFMpeg\FFMpeg;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class VideoControllerDefault extends Controller
{
    public function upload(Request $request)
    {
        //cette fonction ne va pas marcher car j'ai utiliser un outil externe pour avoir la durer de la video ffmpeg et ffprobe
        //si vous voulez quelle fonctionne installer et indexer la 
        //la fonction upload une nouvelle video avec un nom est une formation attribuer et donne une durer
        if ($request->user()->role == "user") {
            return response()->json([
                "status" => 400,
                "message" => "You do not have the permission to do that"
            ]);
        } else if ($request->user()->role == "admin") {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'video' => 'required|mimes:mp4,mov,avi,wmv|max:102400', 
                'course_id' => 'required|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => $validator->errors(),
                ],422);
            }
            
            $videoName = uniqid() . '_' . time() . '.' . $request->video->getClientOriginalExtension();
            
            
            
            
            if($course = Course::find($request->course_id))
            {
                $video = new Video();
            $video->name = $request->name;
            $video->path = $videoName;



            if ($course->videos()->count() > 0) {
                $video->position = $course->videos()->orderBy('position')->max('position') + 1;
            } else {
                $video->position = 1;
            }
            $request->video->move(resource_path('videos'), $videoName);
            
            $ffmpeg = FFMpeg::create([
                'ffmpeg.binaries' => 'C:/ffmpeg-7.0.1-essentials_build/bin/ffmpeg.exe',
                'ffprobe.binaries' => 'C:/ffmpeg-7.0.1-essentials_build/bin/ffprobe.exe', 
            ]);
            

            $videoPath = resource_path('videos/' . $videoName);

            $videoFile = $ffmpeg->open($videoPath);
            

            $duration = $videoFile->getFFProbe()->format($videoPath)->get('duration');
            
            $video->course_id = $request->course_id;
            $video->duration = $duration; 
            $video->save();
            
            $course->duration += $duration;
            $course->save();
            
            return response()->json([
                'message' => 'video uploaded successfully',
            ], 200);
        }
        else{
            return response()->json([
                'message'=> 'this course doesnt exist',
            ]);
        }
    }
    
    }

    public function show(Request $request, $videoname){

        //affiche la video si il a acheter la formation associer et si admin le laisse regarder
        if ($request->user()->role == "user") {
        $userid = $request->user()->id;
        $useracss = User::find($userid)->purchases()->with('items.courses.videos')->get()->pluck('items')->flatten()->pluck('courses')->flatten()->pluck('videos')->unique();
        if($useracss->isEmpty()){
            return response()->json([
                'status'=> 404,
                'message'=> 'you didnt buy this video',
            ]);
            
        }
        else{
            if(File::exists(resource_path("videos/".$videoname))){
            return response()->file(resource_path('videos/'.$videoname));
            }
            else{
                return response()->json([
                    'error'=> 'video not found'
                    ],404);
            }
        }
    }
    else if ($request->user()->role == "admin") {
        if(File::exists(resource_path("videos/".$videoname))){
        return response()->file(resource_path('videos/'.$videoname));
        }
        else{
            return response()->json([
                'error'=> 'video not found'
                ],404);
        }
    }
        
    }

    public function info(Request $request, $id){
        //regarde les info d'une video specifique si admin
        if ($request->user()->role == 'user') {
            return response()->json([
                'error'=> 'you dont have the autorization to do that',
                ],401);
            }
            else if ($request->user()->role == 'admin') {
                $video = Video::find($id);

                if($video){
                    $videocount = Video::where('course_id', $video->course_id)->count();
                    $video->videomax = $videocount;
                    return response()->json($video)
                ->setStatusCode(200);
                }
                else{
                    return response()->json([
                        'error'=> 'video not found'
                        ],404);
                }
            }
    } 
    public function update(Request $request, $id)
    {
        //update la video si la video change update la durer dans la formation associer
    if ($request->user()->role == "user") {
        return response()->json([
            "message" => "You do not have the permission to do that"
        ], 401);
    } else if ($request->user()->role == "admin") {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string',
            'video' => 'sometimes|mimes:mp4,mov,avi,wmv|max:102400',
            'position' => 'sometimes|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors(),
            ], 422);
        }

        $video = Video::findOrFail($id);

        $currentPosition = $video->position;
        $courseId = $video->course_id;

        $video->fill($validator->validated());

        if ($request->has('position')) {
            $targetPosition = $request->position;

            // Check if the target position is within the valid range
            $maxPosition = Video::where('course_id', $courseId)->max('position');
            if ($targetPosition > $maxPosition) {
                return response()->json([
                    'error' => 'The value is more than the number of videos'
                ], 400);
            }

            // Update positions of other videos
            if ($targetPosition < $currentPosition) {
                // Move up: decrement positions of videos between target and current positions
                Video::where('course_id', $courseId)
                    ->where('position', '>=', $targetPosition)
                    ->where('position', '<', $currentPosition)
                    ->increment('position');
            } elseif ($targetPosition > $currentPosition) {
                // Move down: increment positions of videos between current and target positions
                Video::where('course_id', $courseId)
                    ->where('position', '<=', $targetPosition)
                    ->where('position', '>', $currentPosition)
                    ->decrement('position');
            }

            $video->position = $targetPosition;
        }

        if ($request->has('video')) {
            $videoName = uniqid() . '_' . time() . '.' . $request->video->getClientOriginalExtension();

            $request->video->move(resource_path('videos'), $videoName);

            // Initialize FFMpeg with specified paths
            $ffmpeg = FFMpeg::create([
                'ffmpeg.binaries' => 'C:/ffmpeg-7.0.1-essentials_build/bin/ffmpeg.exe', // Adjust the path as needed
                'ffprobe.binaries' => 'C:/ffmpeg-7.0.1-essentials_build/bin/ffprobe.exe', // Adjust the path as needed
            ]);

            // Get the full path of the uploaded video
            $videoPath = resource_path('videos/' . $videoName);

            // Open the video file
            $videoFile = $ffmpeg->open($videoPath);

            // Get the duration in seconds
            $duration = $videoFile->getFFProbe()->format($videoPath)->get('duration');

            if (unlink(resource_path('videos/' . $video->path))) {
                $course = Course::where('id', $video->course_id)->first();
                $course->duration -= $video->duration;
                $course->duration += $duration;
                $course->save();
                $video->duration = $duration;
                $video->path = $videoName;
            } else {
                return response()->json([
                    'error' => 'An error occurred while deleting the previous video'
                ], 500);
            }
        }

        $video->save();

        return response()->json([
            'message' => 'Updated successfully',
        ], 200);
    }
}


public function destroy(Request $request,$id){
//supprime la video si admin
 if($request->user()->role =='admin'){

    $video = Video::findOrFail( $id );
    $videopath = $video->path;
    $courseId = $video->course_id;
    $maxPosition = Video::where('course_id', $video->course_id)->max('position');
    $videopos = $video->position;
    DB::beginTransaction();

    if($video->delete())
    {
        if(unlink(resource_path('videos/'. $videopath)))
        {
            if($videopos != $maxPosition)
            {
                Video::where('course_id', $courseId)
                    ->where('position', '>', $videopos)
                    ->decrement('position');
            }
            DB::commit();
        return response()->json([
            'message'=> 'video deleted successfully'
            ],200); 
    }
    else{
        DB::rollBack();
        return response()->json([
            'error'=> 'video deletetion had a problem sorry'
            ],404);
    }
}
else{
    DB::rollBack();
        return response()->json([
            'error'=> 'video deletetion had a problem sorry'
            ],404);
    }
}
 }


}
