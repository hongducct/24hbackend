<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class BankController extends Controller
{
    // Tương tự như OrderStatusController, thay OrderStatus bằng Bank
    public function index(): JsonResponse
    {
        $banks = Bank::all();
        return response()->json($banks);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string', // Nên thêm validation cho định dạng account_number
            'account_name' => 'required|string',
        ]);

        // Kiểm tra xem user đã có tài khoản ngân hàng chưa
        $existingBank = Bank::where('user_id', $request->user_id)->first();

        if ($existingBank) {
            return response()->json([
                'message' => 'User already has a bank account.',
            ], Response::HTTP_CONFLICT); // Status code 409 Conflict
        }

        try {
            $bank = Bank::create($request->all());
            return response()->json($bank, Response::HTTP_CREATED);
        } catch (Throwable $e) {
            \Log::error($e);
            return response()->json([
                'message' => 'Failed to create bank account.',
                'error' => $e->getMessage(), // Chỉ trả về message lỗi trong môi trường dev
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(Bank $bank): JsonResponse
    {
        return response()->json($bank);
    }

    public function update(Request $request, Bank $bank): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:banks,name,' . $bank->id,
            // Các validation khác
        ]);
        $bank->update($request->all());
        return response()->json($bank);
    }

    public function destroy(Bank $bank): JsonResponse
    {
        $bank->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}