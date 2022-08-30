<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    //
    public function register(Request $request){
        $validator = Validator::make($request->all(),[
            'name'=> 'required|string',
            'email'=> 'required|string|unique:users|email',
            'password'=>'required|confirmed|string|min:6|confirmed|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/',
            'phone'=> 'required|string'
        ]);
        if($validator->fails()){
            return response()->json([
                'status'=> 401,
                'message'=> 'Validation Fails',
                'content'=> $validator->messages()
            ],401);
        }
        $user = User::create([
            'name'=> $request->name,
            'email'=> $request->email,
            'password'=> bcrypt($request->password),
            'phone'=> $request->phone,
            'role_id'=> 2
        ]);
        $token = $user->createToken($user->email.'_UserToken', [''])->plainTextToken;
        event(new Registered($user));
        return response()->json([
            'status'=> 200,
            'message'=> 'Registered Successfullly',
            'content'=> $token
        ],200);
    }

    public function login(Request $request){
        $validator = Validator::make($request->all(),[
            'email'=> 'required|string|email',
            'password'=> 'required|string' 
        ]);
        if($validator->fails()){
            return response()->json([
                'status' => 401,
                'message'=> 'Validation Fails',
                'content'=> $validator->messages()
            ],401);
        }else{
            $user = User::where('email', $request->email)->first();
            if(!$user || !Hash::check($request->password, $user->password)){
                return response()->json([
                    'status'=> 401,
                    'message'=> 'Username or Password is incorrect',
                ],401);
            }
            $user->tokens()->delete();
            if($user->role_id == 1){
                $role = 'Admin';
                $token = $user->createToken($user->email.'_AdminToken', ['server:admin'])->plainTextToken;
            }else{
                $role = 'User';
                $token = $user->createToken($user->email.'_UserToken', [''])->plainTextToken;
            }
            $user->token = $token;
            $user->role = $role;
            return response()->json([
                'status'=> 200,
                'message'=> 'Login Successfully',
                'content'=> $user
            ],200);
        }
    }

    public function logout(Request $request){
        $request->user()->tokens()->delete();
        return response()->json([
            'status'=> 200,
            'message'=> 'Logout Successfully'
        ]);
    }
}
