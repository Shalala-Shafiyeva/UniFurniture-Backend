<?php

namespace App\Http\Controllers;

use App\Models\BasketProduct;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function store(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'address' => 'required|string|max:255',
            'payment_type' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $basketId = $user->basket->id;
        $basketProducts = BasketProduct::where('basket_id', $basketId)->get();
        $total = 0;
        //orders table
        $newOrder =  new Order();
        $newOrder->user_id = $user->id;
        $newOrder->basket_id = $basketId;
        $newOrder->address = $req->address;
        $newOrder->payment_type = $req->payment_type;
        $newOrder->total = 0;
        $newOrder->uid = uniqid();
        $newOrder->save();


        foreach ($basketProducts as $basketProduct) {
            $product = Product::find($basketProduct->product_id);
            $total += $basketProduct->stock * $product->price;
            //order_details table
            $orderDetailsBody = new OrderDetail();
            $orderDetailsBody->order_id = $newOrder->id;
            $orderDetailsBody->product_id = $basketProduct->product_id;
            $orderDetailsBody->quantity = $basketProduct->qty;
            $orderDetailsBody->price = $product->price;
            $orderDetailsBody->total = $product->price * $basketProduct->stock;
            $orderDetailsBody->save();
        }

        $newOrder->total = $total;
        $newOrder->save();

        BasketProduct::where('basket_id', $basketId)->delete(); //eger bu mehsul sifaris edilibse onlari sil basketden

        return response()->json([
            'message' => "Order created successfully",
            'success' => true
        ]);
    }
}
