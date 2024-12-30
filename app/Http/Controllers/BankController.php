<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;
use Illuminate\Validation\Rule;

class BankController extends Controller
{
    public function index(): JsonResponse
    {
        $banks = Bank::all();
        return response()->json($banks);
    }

    public function getBankByUserId($user_id): JsonResponse
    {
        $banks = Bank::where('user_id', $user_id)->get();
        return response()->json($banks);
    }

    public function store(Request $request): JsonResponse
    {
        // dd($request->all());
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string', // Thêm unique validation
            'account_name' => 'required|string',
            'address' => 'required|string',
            'site' => 'required|string',
            'bankaccount' => 'required|string',
            'tel' => 'required|string',
        ]);

        // Kiểm tra xem user đã có tài khoản ngân hàng chưa (kiểm tra theo cả account_number)
        $existingBank = Bank::where('user_id', $request->user_id)
            ->where('account_number', $request->account_number)
            ->first();

        if ($existingBank) {
            return response()->json([
                'message' => 'User already has a bank account with this account number.',
            ], Response::HTTP_CONFLICT);
        }
        try {
            $bank = Bank::create($request->all());
            return response()->json($bank, Response::HTTP_CREATED);
        } catch (Throwable $e) {
            \Log::error($e);
            return response()->json([
                'message' => 'Failed to create bank account.',
                'error' => config('app.debug') ? $e->getMessage() : 'Server error', // Chỉ hiển thị lỗi chi tiết trong môi trường dev
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
            'bank_name' => 'required|string|max:255',
            'account_number' => [
                'required',
                'string',
                Rule::unique('banks')->ignore($bank->id),
            ],
            'account_name' => 'required|string',
            'address' => 'nullable|string',
            'site' => 'nullable|string',
            'bankaccount' => 'nullable|string',
            'tel' => 'nullable|string',
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