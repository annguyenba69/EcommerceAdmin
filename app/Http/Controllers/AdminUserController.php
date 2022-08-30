<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminUserController extends Controller
{
    public function getAllListUsers(Request $request)
    {
        $listUsers = User::all();
        if ($request->status == 'trash') {
            $listUsers = User::onlyTrashed()->get();
        }
        $numberActiveUsers = User::all()->count();
        $numberTrashUsers = User::onlyTrashed()->count();
        return response()->json([
            'status' => 200,
            'message' => 'Get All List Users Successfullly',
            'content' => [
                'listUsers' => $listUsers,
                'numberActiveUsers' => $numberActiveUsers,
                'numberTrashUsers' => $numberTrashUsers
            ]
        ], 200);
    }

    public function addUser(Request $request)
    {
        $validator = Validator($request->all(), [
            'name' => 'required|string',
            'email' => 'required|string|unique:users|email',
            'password' => 'required|confirmed|string|min:6|confirmed|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/',
            'phone' => 'required|string',
            'role_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 401,
                'message' =>  'Validation Fails',
                'content' => $validator->errors()
            ], 401);
        }
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'phone' => $request->phone,
            'role_id' => $request->role_id
        ]);
        event(new Registered($user));
        return response()->json([
            'status' => 200,
            'message' => 'Registered Successfullly',
        ], 200);
    }

    public function getDetailUserById($id)
    {
        $user = User::find($id);
        if ($user != null) {
            return response()->json([
                'status' => 200,
                'message' => 'Successful user search',
                'content' => $user
            ], 200);
        } else {
            return response()->json([
                'status' => 200,
                'message' => 'No users found',
                'content' => []
            ], 200);
        }
    }

    public function editUser(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user != null) {
            $validator = Validator($request->all(), [
                'name' => 'required|string',
                'email' => 'required|string|email',
                'password' => 'nullable|string|min:6|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/',
                'phone' => 'required|string',
                'role_id' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 401,
                    'message' => 'Validation Fails',
                    'content' => $validator->errors()
                ], 401);
            }
            $password = $user->password;
            if ($request->password) {
                $password = bcrypt($request->password);
            }
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $password,
                'phone' => $request->phone,
                'role_id' => $request->role_id
            ]);
            return response()->json([
                'status' => 200,
                'message' => 'User information update successful',
                'content' => []
            ]);
        } else {
            return response()->json([
                'status' => 200,
                'message' => 'No users found',
                'content' => []
            ], 200);
        }
    }

    public function softDeleteUser($id)
    {
        $user = User::find($id);
        if ($user != null) {
            $user->delete();
            return response()->json([
                'status' => 200,
                'message' => 'Soft Delete user successfully',
                'content' => []
            ], 200);
        } else {
            return response()->json([
                'status' => 200,
                'message' => 'No users found',
                'content' => []
            ], 200);
        }
    }

    public function forceDelete($id)
    {
        $user = User::onlyTrashed()->find($id);
        if ($user != null) {
            $user->forceDelete();
            return response()->json([
                'status' => 200,
                'message' => 'Permanently delete successful user',
                'content' => []
            ], 200);
        } else {
            return response()->json([
                'status' => 200,
                'message' => 'No users found in trash',
                'content' => []
            ], 200);
        }
    }

    public function findUserByKeyword(Request $request)
    {
        $listUsers = [];
        $keyword = $request->keyword;
        if ($request->status == 'trash') {
            $listUsers = User::onlyTrashed()->where('name', 'LIKE', "%$keyword%")->get();
        } else {
            $listUsers = User::where('name', 'LIKE', "%$keyword%")
                ->orWhere('email', 'LIKE', "%$keyword%")->get();
        }
        return response()->json([
            'status' => 200,
            'message' => 'Successful user search',
            'content' => $listUsers
        ], 200);
    }

    public function restoreUser($id)
    {
        $user = User::onlyTrashed()->find($id);
        if ($user != null) {
            $user->restore();
            return response()->json([
                'status' => 200,
                'message' => 'Account restore successful',
                'content' => []
            ], 200);
        } else {
            return response()->json([
                'status' => 200,
                'message' => 'No users found in trash',
                'content' => []
            ], 200);
        }
    }
}
