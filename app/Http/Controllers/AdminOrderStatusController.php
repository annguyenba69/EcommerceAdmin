<?php

namespace App\Http\Controllers;

use App\Models\OrderStatus;
use Illuminate\Http\Request;

class AdminOrderStatusController extends Controller
{
    public function getAllOrderStatus(){
        $orderStatus = OrderStatus::all();
        return response()->json([
            'status'=> 200,
            'message'=> 'Get All Order Status Successfully',
            'content'=> $orderStatus
        ], 200);
    }
}
