<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class AdminRoleController extends Controller
{
    public function getAllListRoles(){
        $listRoles = Role::all();
        return response()->json([
            'status'=> 200,
            'message'=> 'Get All List Roles Successfully',
            'content'=> $listRoles
        ],200);
    }
}
