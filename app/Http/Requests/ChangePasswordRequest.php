<?php

namespace App\Http\Requests;

use App\Rules\CurrentPasswordRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
{
	public function rules(): array
	{
		return [
			'current_password' => ['required', new CurrentPasswordRule()],
			'password' => ['required', Password::min(6), 'confirmed'],
		];
	}

	public function messages()
	{
		return [
			'password.min' => __('validation.password_min'),
			'password.confirmed' => __('validation.password_mismatch'),
		];
	}
}
