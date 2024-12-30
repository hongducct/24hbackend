<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Hoặc điều kiện authorize phù hợp
    }

    public function rules()
    {
        return [
            'name' => 'nullable|string|max:255',
            'email' => [
                'nullable',
                'email',
                Rule::unique('users')->ignore($this->user->id),
            ],
            'username' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('users')->ignore($this->user->id),
            ],
            'invitation_code' => 'nullable|exists:invitations,code',
            'avatar' => 'nullable|string', // Hoặc rule phù hợp với avatar
            'commission' => 'nullable|numeric', // Hoặc rule phù hợp
            'level' => 'nullable|integer', // Hoặc rule phù hợp
        ];
    }

    public function messages()
    {
        return [
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
    }
}