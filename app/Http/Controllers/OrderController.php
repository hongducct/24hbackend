<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class OrderController extends Controller
{
    public function index(): JsonResponse
    {
        $orders = Order::with(['user', 'orderStatus', 'orderDetails.product'])->get();
        return response()->json($orders);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'order_status_id' => 'required|exists:order_statuses,id',
            'total_amount' => 'required|numeric|min:0',
            'shipping_address' => 'required|string',
            'payment_method' => 'nullable|string',
            'shipping_method' => 'nullable|string',
            'order_date' => 'nullable|date',
            'bonus_amount' => 'nullable|numeric|min:0', // Validation cho bonus_amount
            'order_details' => 'required|array', // Vẫn cần validation cho order_details
            'order_details.*.product_id' => 'required|exists:products,id',
            'order_details.*.quantity' => 'required|integer|min:1',
            'order_details.*.price' => 'required|numeric|min:0',
        ]);

        $order = Order::create($request->except('order_details'));

        foreach ($request->input('order_details') as $orderDetailData) {
            $order->orderDetails()->create($orderDetailData);
        }

        return response()->json($order, Response::HTTP_CREATED);
    }

    public function show(Order $order): JsonResponse
    {
        $order->load(['user', 'orderStatus', 'orderDetails.product']);
        return response()->json($order);
    }

    public function update(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'user_id' => 'exists:users,id',
            'order_status_id' => 'exists:order_statuses,id',
            'total_amount' => 'numeric|min:0',
            'shipping_address' => 'string',
            'payment_method' => 'nullable|string',
            'shipping_method' => 'nullable|string',
            'order_date' => 'nullable|date',
            'bonus_amount' => 'nullable|numeric|min:0', // Validation cho bonus_amount
        ]);

        $order->update($request->all());

        // Xử lý cập nhật order_details (nếu có)
        if ($request->has('order_details')) {
            $request->validate([
                'order_details.*.id' => 'exists:order_details,id',
                'order_details.*.product_id' => 'exists:products,id',
                'order_details.*.quantity' => 'integer|min:1',
                'order_details.*.price' => 'numeric|min:0',
            ]);

            foreach ($request->input('order_details') as $orderDetailData) {
                if(isset($orderDetailData['id'])){
                    $order->orderDetails()->find($orderDetailData['id'])->update($orderDetailData);
                }else{
                    $order->orderDetails()->create($orderDetailData);
                }
            }
        }

        return response()->json($order);
    }

    public function destroy(Order $order): JsonResponse
    {
        $order->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function orderDetails(Order $order): JsonResponse
    {
        $orderDetails = $order->orderDetails()->with('product')->get();
        return response()->json($orderDetails);
    }
}