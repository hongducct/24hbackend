<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class ChangePaymentPasswordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'current_payment_password' => ['required', function ($attribute, $value, $fail) {
                if (!Hash::check($value, $this->user->payment_password)) {
                    $fail('Mật khẩu thanh toán hiện tại không chính xác.');
                }
            }],
            'payment_password' => 'required|min:6|confirmed',
        ];
    }

    public function messages()
    {
        return [
            'current_payment_password.required' => 'Vui lòng nhập mật khẩu thanh toán hiện tại.',
            'payment_password.required' => 'Vui lòng nhập mật khẩu thanh toán mới.',
            'payment_password.min' => 'Mật khẩu thanh toán mới phải có ít nhất 6 ký tự.',
            'payment_password.confirmed' => 'Xác nhận mật khẩu thanh toán mới không khớp.',
        ];
    }
}