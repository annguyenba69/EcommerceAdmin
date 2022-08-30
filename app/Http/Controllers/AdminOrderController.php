<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function getAllOrders(Request $request){
        $keyword = "";
        if ($request->keyword) {
            $keyword = $request->keyword;
        };
        if($request->order_status_id != ""){
            $order_status_id = $request->order_status_id; 
            $orders = Order::where('order_status_id',$order_status_id)->orderBy('created_at','desc')->whereHas('user', function(Builder $query) use($keyword){
                $query->where('name', 'LIKE', "%$keyword%");
            })->get();
        }else{
            $orders = Order::orderBy('created_at','desc')->whereHas('user', function(Builder $query) use($keyword){
                $query->where('name', 'LIKE', "%$keyword%");
            })->get();
        }

        foreach($orders as $key=>$order){
            $order->orderStatus;
            $order->paymentMethod;
            $order->user;
            $order->products;
            $order->total = Order::totalPrice($order->id);
        }
        $numOrder = Order::all()->count();
        $numPending = Order::where('order_status_id', 1)->get()->count();
        $numDelivery = Order::where('order_status_id', 2)->get()->count();
        $numComplete = Order::where('order_status_id', 3)->get()->count();
        $numCancel = Order::where('order_status_id', 4)->get()->count();
        return response()->json([
            'status'=>200,
            'message'=>'Get All Orders Successfully',
            'content'=> [
                'listOrders'=> $orders,
                'num'=> [
                    'numOrder'=> $numOrder,
                    'numPending'=> $numPending,
                    'numDelivery'=> $numDelivery,
                    'numComplete'=> $numComplete,
                    'numCancel'=> $numCancel
                ]
            ]
        ],200);
    }

    public function getDetailOrder($id){
        $order = Order::find($id);
        $infoProduct = [];
        foreach($order->products as $key=>$product){
            $product->productThumbnails;
            array_push($infoProduct, $product);
        }
        if($order != null){
            $order->user;
            $order->products = $infoProduct;
            $order->total = Order::totalPrice($order->id);
            return response()->json([
                'status'=>200,
                'message'=> 'Get Detail Order Successfully',
                'content'=> $order
            ],200);
        }else{
            return response()->json([
                'status'=>400,
                'message'=> 'No Order Found',
                'content'=> []
            ],400);
        }
    }

    public function editOrder(Request $request){
        $order = Order::find($request->id);
        if($order != null){
            $order->update([
                'order_status_id'=> $request->order_status_id
            ]);
            return response()->json([
                'status'=>200,
                'message'=> 'Update Order Successfully',
                'content'=>[]
            ]);
        }else{
            return response()->json([
                'status'=>400,
                'message'=> 'No Order Found',
                'content'=>[]
            ]);
        }
    }
}
