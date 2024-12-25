<?php

namespace App\Http\Controllers;

use App\Models\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class OrderStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $orderStatuses = OrderStatus::all();
        return response()->json($orderStatuses);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:order_statuses,name', // Ví dụ validation
            // Thêm các validation khác nếu cần
        ]);

        $orderStatus = OrderStatus::create($request->all());
        return response()->json($orderStatus, Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(OrderStatus $orderStatus): JsonResponse
    {
        return response()->json($orderStatus);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OrderStatus $orderStatus): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:order_statuses,name,' . $orderStatus->id, // Validation update
            // Thêm các validation khác nếu cần
        ]);

        $orderStatus->update($request->all());
        return response()->json($orderStatus);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OrderStatus $orderStatus): JsonResponse
    {
        $orderStatus->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}