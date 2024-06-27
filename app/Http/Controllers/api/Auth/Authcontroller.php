<?php

namespace App\Http\Controllers\api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(LoginRequest $request) : JsonResponse
    {
    //authentifie la personne avec l'email et password er retourne le token d'acces et c'est info
        $request->authenticate();

        $user = auth()->user();
        $token = $request->user()->createToken('authToken')->plainTextToken;
        return response()->json([
            'message' => 'login Successfully',
            'token' => $token,
            'user' => $user
        ]);

    }
    public function register(Request $request) : JsonResponse
    {
        //utilise tous les attribus ci dessous pour creer un nouveau utilisateur
        $validate = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'lastname'=>'required|string',
            'firstname'=> 'required|string',

        ]);
        if ($validate->fails()) {
           return response()->json([
            'error'=> $validate->errors(),
            ],422);
        }
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'lastname'=> $request->lastname,
            'firstname'=> $request->firstname,
            'password' =>bcrypt($request->password),
        ]);
        return response()->json([
            'message' => 'Registered Successfully',
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        //supprime le token d'acces de l'utlisateur qui ce deconnecte
        $user = $request->user();
        if($user)
        {
            if($user->currentAccessToken()->delete()){
                return response()->json(['message' => 'Logged out successfully'],200);}
            else{
                return response()->json(['error'=> 'token not found'],404);}
            }
        

    
    else{
        return response()->json(['error'=> 'user not found'],404);}
    }
    
    


    public function loginAdmin(Request $request) 
    {

        //se connecte avec email et password si l'utilisateur est admin le laisse passer sinon 401
        $validate = Validator::make($request->all(), [ 
            'email'=> 'required|string|email',
            'password'=> 'required|string',
            ]);
            if ($validate->fails()) {
                return response()->json([
                    'error'=> $validate->errors(),
                    ],422);
                }
                $user = User::where('email', $request->email)
                ->where('role','admin')->first();
                if ($user) {
                    if( $request->password == $request->password) {
                        return response()->json([
                            'token'=> $user->createToken('authToken')->plainTextToken,
                            'user'=> $user,
                            ],200);
                    } else {
                        return response()->json([
                            'error'=> 'email or password incorrect'
                            ],404);
                    }
                } else {
                    return response()->json([
                        'error'=> 'email or password incorrect',
                        ],404);

                    }
    }  
}
