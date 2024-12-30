<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Invitation; // Import model Invitation
use Illuminate\Support\Str; // Import Str class để tạo chuỗi ngẫu nhiên
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\UserRequest; // Import UserRequest
use Illuminate\Support\Facades\DB; // Import DB để dùng transaction
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken; // Tạo token Sanctum
            return response()->json(['access_token' => $token, 'token_type' => 'Bearer', 'user' => $user], 200);
        } else {
            return response()->json(['error' => 'Invalid Credentials'], 401);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [ // Sử dụng Validator::make()
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users',
            'password' => 'required|min:6',
            'password_confirmation' => 'required|same:password',
            'username' => 'required|string|max:255|unique:users',
            'avatar' => 'nullable|string',
            'commission' => 'nullable|numeric',
            'level' => 'nullable|integer',
            'invitation_code' => 'required|exists:invitations,code',
            'payment_password' => 'required|min:6',
        ],[
            'invitation_code.required' => 'Vui lòng nhập mã mời.',
            'invitation_code.exists' => 'Mã mời không tồn tại.',
            'email.unique' => 'Email đã tồn tại.',
            'username.unique' => 'Tên người dùng đã tồn tại.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'payment_password.min' => 'Mật khẩu thanh toán phải có ít nhất 6 ký tự.',
            'payment_password.required' => 'Vui lòng nhập mật khẩu thanh toán.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password_confirmation.required' => 'Vui lòng xác nhận mật khẩu.',
            'password_confirmation.same' => 'Xác nhận mật khẩu không khớp.',
            // 'payment_password.confirmed' => 'Xác nhận mật khẩu thanh toán không khớp.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422); // Trả về lỗi validation
        }

        DB::beginTransaction();
        try {
            $user = new User();
            // $user->name = $request->input('name'); // Sử dụng $request->input()
            // $user->email = $request->input('email');
            $user->password = Hash::make($request->input('password'));
            $user->payment_password = Hash::make($request->input('payment_password'));
            $user->username = $request->input('username');
            // $user->avatar = $request->input('avatar');
            // $user->commission = $request->input('commission');
            // $user->level = $request->input('level');
            $user->invitation_code = $request->input('invitation_code');
            $user->commission = "0.6";
            $user->save();

            // Tạo mã mời cho người dùng vừa đăng ký
            $code = Str::random(8);
            while (Invitation::where('code', $code)->exists()) {
                $code = Str::random(8);
            }

            $invitation = new Invitation();
            $invitation->code = $code;
            $invitation->user_id = $user->id;
            $invitation->save();

            DB::commit();

            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json(['access_token' => $token, 'token_type' => 'Bearer', 'user' => $user, 'invitation_code' => $invitation->code], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e);
            return response()->json(['message' => 'Failed to register user', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}