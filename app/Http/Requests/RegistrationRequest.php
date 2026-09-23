<?php

namespace App\Http\Requests;

use App\Rules\ReCaptcha;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegistrationRequest extends FormRequest
{
	public function rules(): array
	{
		return [
			'email' => 'required|email|unique:users,email',
			'password' => ['required', Password::min(6), 'confirmed'],
			'captcha' => ['required', new ReCaptcha()],
		];
	}

	public function messages()
	{
		return [
			'email.unique' => __('validation.email_in_use'),
			'password.min' => __('validation.password_min'),
			'password.confirmed' => __('validation.password_mismatch'),
		];
	}
}
