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
            'current_password' => 'required_with:password',
            'password' => 'nullable|min:6|confirmed',
            'payment_password' => 'nullable|min:6|confirmed',
        ];
    
        $messages = [
            'current_password.required_with' => 'Vui lòng nhập mật khẩu hiện tại nếu muốn thay đổi mật khẩu.',
            'password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
            'payment_password.min' => 'Mật khẩu thanh toán phải có ít nhất 6 ký tự.',
            'payment_password.confirmed' => 'Xác nhận mật khẩu thanh toán không khớp.',
        ];
    
        if ($request->filled('name')) {
            $rules['name'] = 'required|string|max:255';
            $messages['name.required'] = 'Vui lòng nhập tên.';
        }
    
        if ($request->filled('email')) {
            $rules['email'] = [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ];
            $messages['email.required'] = 'Vui lòng nhập email.';
            $messages['email.email'] = 'Email không đúng định dạng.';
            $messages['email.unique'] = 'Email đã tồn tại.';
        }
        if ($request->filled('username')) {
            $rules['username'] = [
                'nullable',
                'string',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ];
            $messages['username.unique'] = 'Tên người dùng đã tồn tại.';
        }
        if ($request->filled('invitation_code')) {
            $rules['invitation_code'] = 'nullable|exists:invitations,code';
            $messages['invitation_code.exists'] = 'Mã mời không chính xác.';
        }
    
        $validator = Validator::make($request->all(), $rules, $messages);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json(['errors' => ['current_password' => ['Mật khẩu hiện tại không chính xác.']]], 422);
            }
            $user->password = Hash::make($request->password);
        }
        if ($request->filled('payment_password')) {
            $user->payment_password = Hash::make($request->payment_password);
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