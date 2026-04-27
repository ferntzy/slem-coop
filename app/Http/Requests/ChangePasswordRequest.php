<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Enter your current temporary password.',
            'current_password.current_password' => 'The current password does not match the one we have on file.',
            'password.required' => 'Enter a new password.',
            'password.min' => 'Your new password must be at least 8 characters long.',
            'password.confirmed' => 'Confirm your new password so we know it is correct.',
            'password.different' => 'Your new password must be different from the current one.',
        ];
    }
}
