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


########################################################### MESSAGE ###########################################################
//j'ais utiliser middleware pour proteger mes route et pour verifier si l'utilisateur avais accesser a cette fonction

//formation => course

//MERCI
########################################################### MESSAGE ###########################################################





########################################################### AUTHENTIFICATION ###########################################################

// authentification utilisateur (user)
Route::post('/login',[AuthController::class, 'login']);
Route::post('/register',[AuthController::class, 'register']);



// authentification utilisateur (admin)
Route::post('/admin/login', [AuthController::class,'loginAdmin']);


// deconnexion de l'utilisateur (admin+user)
Route::middleware('auth:sanctum')->post('/logout',[AuthController::class,'logout']);


########################################################### AUTHENTIFICATION ###########################################################






########################################################### MANAGMENT DES VIDEO ###########################################################


 //route pour ajouter une video a une formation specifique
Route::middleware('auth:sanctum')->post('/video/add',[VideoControllerDefault::class,'upload']);


//route pour streamer une video specifique si la personne a acheter la formation associer
Route::middleware('auth:sanctum')->get('/watch/{videoname}',[VideoControllerDefault::class,'show']);


//route pour changer les attribus de la video nom,video,position de la video dans la formation
Route::middleware('auth:sanctum')->post('/videos/{id}/update',[VideoControllerDefault::class,'update']);


//route pour avoir les information d'une video specifique nom,le chemin de la video...
Route::middleware('auth:sanctum')->get('/videos/{id}',[VideoControllerDefault::class,'info']);


//route pour supprimer une video si supprimer update la durrer de la formation
Route::middleware('auth:sanctum')->post('/video/remove/{id}',[VideoControllerDefault::class,'destroy']);

########################################################### MANAGMENT DES VIDEO ###########################################################



########################################################### ROUTE UTILISATEUR ###########################################################

//route pour changer les attribus de l'utilisateur authentifier
Route::middleware('auth:sanctum')->post('/user/update',[UserController::class,'update']);

//route pour changer le mot de passe de l'utilisateur authentifier
Route::middleware('auth:sanctum')->post('/user/password/update',[UserController::class,'updatepassword']);

//pour avoir les info de l'utilisateur authentifier
Route::middleware('auth:sanctum')->get('/user/info',[UserController::class,'userinf']);

//pour avoir les formation acheter par l'utilisateur authentifier
Route::middleware('auth:sanctum')->get('/user/courses',[UserController::class,'usercourses']);

//pour avoir les info de toutes les video dans une formation si l'utilisateur la acheter au prealable
Route::middleware('auth:sanctum')->get('/user/courses/{courseId}/videos',[UserController::class,'courseVideos']);


//route pour l'admin qui donne tous les utilisateur inscris
Route::middleware('auth:sanctum')->get('/users/index',[UserController::class,'userindex']);

//utiliser pour acheter les formationd dans le cart j'avais fait un systeme qui detecte si la carte etais une visa ou mastercar
//mais j'ai du les supprimer car on avais pas le temp
Route::middleware('auth:sanctum')->post('/user/checkout', [CheckoutController::class, 'store']);

//verifie le role de l'utilisateur si admin retourne 200 sinon 401
Route::middleware('auth:sanctum')->get('/user/role', [UserController::class,'rolecheck']);

//pour l'admin retourne tous les historique d'achat de tous les utilisateur
Route::middleware('auth:sanctum')->get('/invoices', [UserController::class,'indexInvoice']);

//retourne l'historique de l'utilisateur authentifier
Route::middleware('auth:sanctum')->get('/user/history', [UserController::class,'userInvoices']);

########################################################### ROUTE UTILISATEUR ###########################################################








########################################################### MANAGMENT DES COURSE ###########################################################


//pour avoir toutes les course existante na pas besoin d'authentification
Route::get('/courses',[CourseController::class,'index']);

//utiliser par la search bar de react pour retouner les formation qui ressemble a l'input envoyer (cherche avec le nom)
Route::post('/courses/search/{name}',[CourseController::class,'search']);

//retourne lES info de la  formation avec l'id envoyer 
Route::get('/courses/{id}',[CourseController::class,'indexId']);

//donne les video de la formation avec le meme id marche que pour l'admin
Route::middleware('auth:sanctum')->get('/courses/{id}/videos',[CourseController::class,'coursesvideos']);

//ajoute une nouvelle formation marche que pour l'admin
Route::middleware('auth:sanctum')->post('/courses/add',[CourseController::class,'add']);

//supprime une formation marche que pour l'admin
Route::middleware('auth:sanctum')->post('/courses/{id}/remove',[CourseController::class, 'remove']);

//change les attribus d'une formation deja existante marche que pour l'admin
Route::middleware('auth:sanctum')->post('/courses/{id}/update',[CourseController::class, 'update']);

//permet d'acceder au photo de l'application
Route::get('/images/{name}',[CourseController::class,'picindex']);

########################################################### MANAGMENT DES COURSE ###########################################################







########################################################### MANAGMENT DES CATEGORIE ###########################################################

//route qui donne toute les categorie existante
Route::get('/category',[CategoryController::class,'index']);

//route qui donne les info de la categorie avec l'id envoyer
Route::get('/category/{id}',[CategoryController::class,'indexId']);

//ajoute une nouvelle categorie 
Route::middleware('auth:sanctum')->post('/category/add',[CategoryController::class,'add']);
 
//change les attribus d'une categorie existante
Route::middleware('auth:sanctum')->post('/category/{id}/update',[CategoryController::class, 'update']);

//supprime une categorie ne peux pas supprimer si l'id == 1 car normalment la premiere categorie et la categorie other donc non supprimable
Route::middleware('auth:sanctum')->post('/category/{id}/remove',[CategoryController::class, 'destroy']);

//donne les fomation sous la category avec la meme id pas utiliser car react filtre localment par categorie
Route::get('categories/{categoryId}/courses', [CourseController::class,'getCoursesByCategory']);


##################################################### MANAGMENT DES CATEGORIE #####################################################






##################################################### ROUTE NON IMPLEMETER MAIS FONCTIONEL #####################################################


//non utiliser mais fonctionel sur postman envoie un email avec un lien de resetpassword http://127.0.0.1:3000/resetpassword/{token}
//le token etais generer est enregistrer dans la base de donner 
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);


//non utiliser mais fonctionel pour changer son mot de passe si le token est envoyer avec
Route::post('/reset-password', [ResetPasswordController::class, 'reset']);


##################################################### ROUTE NON IMPLEMETER MAIS FONCTIONEL #####################################################








