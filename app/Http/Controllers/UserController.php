<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Invitation; // Import model Invitation
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest; // Import request validate
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator; // Import Validator
use Illuminate\Support\Str; // Import Str class để tạo chuỗi ngẫu nhiên
use Illuminate\Support\Facades\DB; // Import DB để dùng transaction
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all(); // Hoặc phân trang: User::paginate(10);
        return response()->json($users);
    }

    public function store(UserRequest $request)
    {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->username = $request->username;
        $user->avatar = $request->avatar;
        $user->commission = $request->commission;
        $user->level = $request->level;
        $user->invitation_code = $request->invitation_code;

        if ($request->filled('payment_password')) { // Kiểm tra xem payment_password có được gửi lên không
            $user->payment_password = Hash::make($request->payment_password); // Băm mật khẩu thanh toán
        }

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

        return response()->json($user, 201);
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'nullable|string|max:255',
            'email' => [
                'nullable',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'username' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'invitation_code' => 'nullable|exists:invitations,code',
            'avatar' => 'nullable|string', // Hoặc rule phù hợp với avatar
            'commission' => 'nullable|numeric', // Hoặc rule phù hợp
            'level' => 'nullable|integer', // Hoặc rule phù hợp
        ];

        $messages = [
            'name.string' => 'Tên phải là chuỗi ký tự.',
            'name.max' => 'Tên không được vượt quá 255 ký tự.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email đã tồn tại.',
            'username.string' => 'Tên người dùng phải là chuỗi ký tự.',
            'username.max' => 'Tên người dùng không được vượt quá 255 ký tự.',
            'username.unique' => 'Tên người dùng đã tồn tại.',
            'invitation_code.exists' => 'Mã mời không chính xác.',
            // Thêm messages cho avatar, commission, level nếu cần
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->name = $request->input('name', $user->name);
        $user->email = $request->input('email', $user->email);
        $user->username = $request->input('username', $user->username);
        $user->avatar = $request->input('avatar', $user->avatar);
        $user->commission = $request->input('commission', $user->commission);
        $user->level = $request->input('level', $user->level);
        $user->invitation_code = $request->input('invitation_code', $user->invitation_code);

        $user->save();

        return response()->json($user);
    }

    public function changePassword(Request $request, User $user)
    {
        $rules = [
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ];

        $messages = [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['errors' => ['current_password' => ['Mật khẩu hiện tại không chính xác.']]], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'Password changed successfully.'], 200);
    }

    public function changePaymentPassword(Request $request, User $user)
    {
        $rules = [
            'current_payment_password' => 'required',
            'payment_password' => 'required|min:6|confirmed',
        ];

        $messages = [
            'current_payment_password.required' => 'Vui lòng nhập mật khẩu thanh toán hiện tại.',
            'payment_password.required' => 'Vui lòng nhập mật khẩu thanh toán mới.',
            'payment_password.min' => 'Mật khẩu thanh toán mới phải có ít nhất 6 ký tự.',
            'payment_password.confirmed' => 'Xác nhận mật khẩu thanh toán mới không khớp.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!Hash::check($request->current_payment_password, $user->payment_password)) {
            return response()->json(['errors' => ['current_payment_password' => ['Mật khẩu thanh toán hiện tại không chính xác.']]], 422);
        }

        $user->payment_password = Hash::make($request->payment_password);
        $user->save();

        return response()->json(['message' => 'Payment password changed successfully.'], 200);
    }
    
    
    public function show(User $user)
    {
        return response()->json($user);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(null, 204); // 204 No Content
    }
}