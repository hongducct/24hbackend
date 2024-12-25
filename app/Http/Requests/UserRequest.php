<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Hoặc logic phân quyền của bạn
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->user?->id,
            'password' => $this->isMethod('POST') ? 'required|min:6' : 'nullable|min:6',
            'username' => 'nullable|string|max:255|unique:users,username,' . $this->user?->id,
            'avatar' => 'nullable|string',
            'commission' => 'nullable|numeric',
            'level' => 'nullable|integer',
        ];
    }
}