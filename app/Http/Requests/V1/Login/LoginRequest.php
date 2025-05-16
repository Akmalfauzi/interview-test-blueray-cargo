<?php

namespace App\Http\Requests\V1\Login;

use App\DTOs\V1\Login\LoginRequestDTO;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email is required',
            'email.email' => 'Please provide a valid email address',
            'password.required' => 'Password is required',
        ];
    }

    /**
     * Convert the request to a DTO.
     */
    public function toDTO(): LoginRequestDTO
    {
        return new LoginRequestDTO(
            email: $this->input('email'),
            password: $this->input('password'),
            remember: (bool) $this->input('remember', false)
        );
    }
} 