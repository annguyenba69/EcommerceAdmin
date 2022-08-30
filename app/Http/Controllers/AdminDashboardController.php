<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function dashboard(){
        $latest = Order::orderBy('created_at', 'desc')->limit(7)->get();
        $totalRevenue = 0;
        foreach($latest as $key=>$order){
            $order->user;
            $totalPrice = Order::totalPrice($order->id);
            $order->totalPrice = $totalPrice;
            $totalRevenue += $totalPrice;
        }
        $pendingOrder = Order::where('order_status_id', 1)->count();
        $deliveryOrder = Order::where('order_status_id', 2)->count();
        $completeOrder = Order::where('order_status_id', 3)->count();
        $cancelOrder = Order::where('order_status_id', 4)->count();
        $recentAddProducts = Product::orderBy('created_at', 'desc')->limit(7)->get();
        foreach($recentAddProducts as $key=>$product){
            $product->productThumbnails;
        }
        return response()->json([
            'status'=>200,
            'message'=> 'Success',
            'content'=> [
                'latest'=>$latest,
                'recentAddProducts'=>$recentAddProducts,
                'numberOrder'=>[
                    'pendingOrder'=>$pendingOrder,
                    'deliveryOrder'=>$deliveryOrder,
                    'completeOrder'=>$completeOrder,
                    'cancelOrder'=>$cancelOrder,
                    'totalRevenue'=> $totalRevenue
                ]
            ]
        ],200);
    }
}
