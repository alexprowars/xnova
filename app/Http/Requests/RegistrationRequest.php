<?php

namespace App\Http\Requests;

use App\Rules\ReCaptcha;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegistrationRequest extends FormRequest
{
	public function rules()
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
			'email.unique' => 'Такой e-mail уже используется',
			'password.min' => 'Пароль должен состоять как минимум из :min символов',
			'password_confirmation.same' => 'Пароли должны быть одинаковыми',
		];
	}
}
