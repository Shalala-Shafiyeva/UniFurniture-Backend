<?php

namespace App\Http\Controllers;

use App\Helper\OrderStatus;
use App\Models\BasketProduct;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{

    public function index()
    {
        $user = Auth::user();
        $orders = Order::with('order_detail')->where('user_id', $user->id)->orderBy('created_at')->get();

        if (!$orders || $orders->isEmpty()) {
            return response()->json([
                "success" => false,
                "message" => "No orders found"
            ]);
        }

        $formattedOrders = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'created_at' => Carbon::parse($order->created_at)->format('Y-m-d'),
                'order_detail' => $order->order_detail,
                "uid" => $order->uid,
                'address' => $order->address,
                'payment_type' => $order->payment_type,
            ];
        });

        return response()->json([
            "data" => $formattedOrders,
            "success" => true,
            "message" => "Orders fetched successfully"
        ]);
    }

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


    //In Dashboard 
    public function confirmed($id)
    {
        $order = Order::findOrFail($id);
        if ($order->status == OrderStatus::PENDING) {
            $order->status = OrderStatus::CONFIRMED;
            $order->save();
            return redirect()->route('dashboard.order.index');
        }
    }

    public function shipped($id)
    {
        $order = Order::findOrFail($id);
        if ($order->status == OrderStatus::CONFIRMED) {
            $order->status = OrderStatus::SHIPPED;
            $order->save();
            return redirect()->route('dashboard.order.index');
        }
    }

    public function delivered($id)
    {
        $order = Order::findOrFail($id);
        if ($order->status == OrderStatus::SHIPPED) {
            $order->status = OrderStatus::DELIVERED;
            $order->save();
            return redirect()->route('dashboard.order.index');
        }
    }

    public function returned($id)
    {
        $order = Order::findOrFail($id);
        if ($order->status == OrderStatus::DELIVERED) {
            $order->status = OrderStatus::RETURNED;
            $order->save();
            return redirect()->route('dashboard.order.index');
        }
    }

    public function canceled($id)
    {
        $order = Order::findOrFail($id);
        if ($order->status == OrderStatus::PENDING) {
            $order->status = OrderStatus::CANCELED;
            $order->save();
            return redirect()->route('dashboard.order.index');
        }
    }

}
