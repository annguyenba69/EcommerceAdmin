<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{

    use HasFactory;

    protected $guarded = [];// chi ra tat ca cac truong duoc fillable

    public static function totalPrice ($order_id){
        $total = 0;
        $listOrderProducts = OrderProduct::all();
        foreach($listOrderProducts as $key=> $orderProduct){
            if($orderProduct->order_id == $order_id){
                $total += $orderProduct->total;
            }
        }
        return $total;
    }

    public function orderStatus(){
        return $this->belongsTo(OrderStatus::class);
    }

    public function paymentMethod(){
        return $this->belongsTo(PaymentMethod::class);
    }
    
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function products(){
        return $this->belongsToMany(Product::class)->withPivot('quantity', 'total');
    }

}
