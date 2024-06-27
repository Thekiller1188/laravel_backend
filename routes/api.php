<?php

use App\Http\Controllers\api\Auth\AuthController;
use App\Http\Controllers\api\category\CategoryController;
use App\Http\Controllers\api\category\checkouController;
use App\Http\Controllers\api\Checkout\CheckoutController;
use App\Http\Controllers\api\courses\CourseController;
use App\Http\Controllers\api\resetpassword\ForgotPasswordController;
use App\Http\Controllers\api\resetpassword\ResetPasswordController;
use App\Http\Controllers\api\testcontroller;
use App\Http\Controllers\api\User\UserController;
use App\Http\Controllers\api\Video\VideoControllerDefault;
use App\Mail\TestMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


 //routes not finished this is all i got for now


//Login and register routes
   //input => email, 
   //output => token+user info ,token is needed for request with middleware
Route::post('/login',[AuthController::class, 'login']);

   //input => email,name,password
   //output => creat user and give info
Route::post('/register',[AuthController::class, 'register']);

Route::post('/admin/login', [AuthController::class,'loginAdmin']);

Route::middleware('auth:sanctum')->post('/logout',[AuthController::class,'logout']);




## NOT FOR MOBILE
//fetching videos and managing them routes
     //input => token,name,video(file mp4),course_id
     //output => creating a video linked to the course
Route::middleware('auth:sanctum')->post('/video/add',[VideoControllerDefault::class,'upload']);
     //input => {videoname} in the link add the token too to check if the user has access to the video
     //output => the video
Route::middleware('auth:sanctum')->get('/watch/{videoname}',[VideoControllerDefault::class,'show']);
     //input => add the {id} of the video u want to modify and u can modify the position of the ideo in the course by adding position 
     //output => the modified video 
Route::middleware('auth:sanctum')->post('/videos/{id}/update',[VideoControllerDefault::class,'update']);
## NOT FOR MOBILE
Route::middleware('auth:sanctum')->get('/videos/{id}',[VideoControllerDefault::class,'info']);
Route::middleware('auth:sanctum')->post('/video/remove/{id}',[VideoControllerDefault::class,'destroy']);





//user profile update 
  //input => token, the modified fields 
  //output => the modified user
Route::middleware('auth:sanctum')->post('/user/update',[UserController::class,'update']);
Route::middleware('auth:sanctum')->post('/user/password/update',[UserController::class,'updatepassword']);
  //input => token
  //output => user info
Route::middleware('auth:sanctum')->get('/user/info',[UserController::class,'userinf']);

Route::middleware('auth:sanctum')->get('/user/courses',[UserController::class,'usercourses']);

Route::middleware('auth:sanctum')->get('/user/courses/{courseId}/videos',[UserController::class,'courseVideos']);

Route::middleware('auth:sanctum')->get('/users/index',[UserController::class,'userindex']);
Route::post('/courses/search/{name}',[CourseController::class,'search']);




## not for mobile
//course managment 

Route::get('/courses',[CourseController::class,'index']);
Route::get('/courses/{id}',[CourseController::class,'indexId']);
Route::middleware('auth:sanctum')->get('/courses/{id}/videos',[CourseController::class,'coursesvideos']);
Route::middleware('auth:sanctum')->post('/courses/add',[CourseController::class,'add']);
Route::middleware('auth:sanctum')->post('/courses/{id}/remove',[CourseController::class, 'remove']);
Route::middleware('auth:sanctum')->post('/courses/{id}/update',[CourseController::class, 'update']);
Route::get('/images/{name}',[CourseController::class,'picindex']);
## not for mobile

//category managment
   //input => name,token
   //output => create a category
Route::get('/category',[CategoryController::class,'index']);
Route::get('/category/{id}',[CategoryController::class,'indexId']);
Route::middleware('auth:sanctum')->post('/category/add',[CategoryController::class,'add']);
   //input => name,token
   //output => update the category of {id}
Route::middleware('auth:sanctum')->post('/category/{id}/update',[CategoryController::class, 'update']);
   //input => name,token
   //output => remove the category of {id}
Route::middleware('auth:sanctum')->post('/category/{id}/remove',[CategoryController::class, 'destroy']);

Route::get('categories/{categoryId}/courses', [CourseController::class,'getCoursesByCategory']);

## not for mobile



//checkout and payment

     //input => token, (optional)card number (12 digits), course_id in an array 
     //output => buy all the courses in the array
Route::middleware('auth:sanctum')->post('/user/checkout', [CheckoutController::class, 'store']);

## optional for mobile
//reset password routes
  //input => email
  //output => send an reset password email if the email exists 
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
  //input => password(confirm the password in you app), resetpassword's (token)
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);





Route::middleware('auth:sanctum')->get('/user/role', [UserController::class,'rolecheck']);

Route::middleware('auth:sanctum')->get('/invoices', [UserController::class,'indexInvoice']);

Route::middleware('auth:sanctum')->get('/user/history', [UserController::class,'userInvoices']);




