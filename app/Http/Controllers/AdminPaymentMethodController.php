<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class AdminPaymentMethodController extends Controller
{
    public function getAllPaymentMethods(){
        $paymentMethods = PaymentMethod::all();
        return response()->json([
            'status'=> 200,
            'message'=> 'Get All Payment Method Successfully',
            'content'=> $paymentMethods
        ],200);
    }
}
